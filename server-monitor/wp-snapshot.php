<?php
/**
 * WordPress 1サイト分の「プラグイン」と「ユーザー」の状態をテキストで出力する（wp-watch.sh から呼ばれる）。
 *
 * 使い方: php wp-snapshot.php <wp-settings.php があるディレクトリ> [--detail]
 *   --detail … ユーザーの登録日時も出力する（点検用。wp-watch.sh の比較には使わない）
 *
 * - WordPress 本体は読み込まず、ファイル一覧と DB を直接見ます。
 *   不正プラグインは all_plugins / pre_user_query フィルターで管理画面や wp-cli から
 *   自分や作成した管理者を隠すことがあるため、WordPress を通さずに確認します。
 * - wp-config.php も実行せず、define() と $table_prefix を字句解析で読み取ります。
 * - 1行1項目・タブ区切りで出力します（並べ替えは呼び出し側）。
 * - PHP 7.0 以上で動きます（Xserver の古い既定 php でも動くように新しい構文は使いません）。
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}
if ( version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
	fwrite( STDERR, 'PHP 7.0 以上が必要です（現在 ' . PHP_VERSION . '）。wp-watch.conf の PHP_BIN を変更してください' . "\n" );
	exit( 2 );
}

$root   = isset( $argv[1] ) ? rtrim( $argv[1], '/' ) : '';
$detail = in_array( '--detail', $argv, true );
if ( $root === '' || ! is_file( $root . '/wp-settings.php' ) ) {
	fwrite( STDERR, "WordPress のディレクトリを指定してください: {$root}\n" );
	exit( 2 );
}

$lines = array();

/* ---------- ファイル（プラグイン / mu-plugins / ドロップイン） ---------- */

$content = $root . '/wp-content';
foreach ( list_entries( $content . '/plugins' ) as $name ) {
	$lines[] = array( 'plugin', $name );
}
foreach ( list_entries( $content . '/mu-plugins' ) as $name ) {
	$lines[] = array( 'mu-plugin', $name );
}
foreach ( list_entries( $content ) as $name ) {
	// wp-content 直下の PHP（advanced-cache.php / object-cache.php / db.php など）は毎回読み込まれる
	if ( substr( $name, -4 ) === '.php' && is_file( $content . '/' . $name ) ) {
		$lines[] = array( 'dropin', $name );
	}
}

/* ---------- DB（ユーザー / 有効化プラグイン） ---------- */

$config_file = $root . '/wp-config.php';
if ( ! is_file( $config_file ) && is_file( dirname( $root ) . '/wp-config.php' ) && ! is_file( dirname( $root ) . '/wp-settings.php' ) ) {
	$config_file = dirname( $root ) . '/wp-config.php'; // wp-config.php は1つ上の階層にも置ける
}
if ( ! is_file( $config_file ) ) {
	fwrite( STDERR, "wp-config.php が見つかりません: {$root}\n" );
	exit( 2 );
}

$config = parse_wp_config( file_get_contents( $config_file ) );
foreach ( array( 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' ) as $key ) {
	if ( ! isset( $config[ $key ] ) ) {
		fwrite( STDERR, "wp-config.php から {$key} を読み取れません: {$config_file}\n" );
		exit( 2 );
	}
}
$prefix = isset( $config['table_prefix'] ) ? $config['table_prefix'] : 'wp_';
if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $prefix ) ) {
	fwrite( STDERR, "テーブル接頭辞が不正です: {$prefix}\n" );
	exit( 2 );
}

if ( ! extension_loaded( 'mysqli' ) ) {
	fwrite( STDERR, "PHP の mysqli 拡張がありません。wp-watch.conf の PHP_BIN を変更してください\n" );
	exit( 2 );
}

mysqli_report( MYSQLI_REPORT_OFF );
list( $host, $port, $socket ) = parse_db_host( $config['DB_HOST'] );
$db = @mysqli_connect( $host, $config['DB_USER'], $config['DB_PASSWORD'], $config['DB_NAME'], $port, $socket );
if ( ! $db ) {
	fwrite( STDERR, 'DB に接続できません: ' . mysqli_connect_error() . "\n" );
	exit( 2 );
}
mysqli_set_charset( $db, 'utf8mb4' );

// ユーザーと権限（マルチサイトのサイト別権限 wp_2_capabilities なども含む）
$like  = str_replace( '_', '\\_', $prefix ) . '%capabilities';
$users = mysqli_query(
	$db,
	"SELECT u.ID, u.user_login, u.user_email, u.user_registered, m.meta_key, m.meta_value
	   FROM `{$prefix}users` u
	   LEFT JOIN `{$prefix}usermeta` m ON m.user_id = u.ID AND m.meta_key LIKE '" . mysqli_real_escape_string( $db, $like ) . "'
	  ORDER BY u.ID"
);
if ( ! $users ) {
	fwrite( STDERR, 'ユーザーを取得できません: ' . mysqli_error( $db ) . "\n" );
	exit( 2 );
}
$found = array();
while ( $row = mysqli_fetch_assoc( $users ) ) {
	$id = $row['ID'];
	if ( ! isset( $found[ $id ] ) ) {
		$found[ $id ] = array( 'login' => $row['user_login'], 'email' => $row['user_email'], 'registered' => $row['user_registered'], 'roles' => array() );
	}
	$key = (string) $row['meta_key'];
	if ( $key === $prefix . 'capabilities' ) {
		$site = '';
	} elseif ( preg_match( '/^' . preg_quote( $prefix, '/' ) . '(\d+)_capabilities$/', $key, $m ) ) {
		$site = $m[1] . ':';
	} else {
		continue;
	}
	$caps = @unserialize( (string) $row['meta_value'], array( 'allowed_classes' => false ) );
	if ( is_array( $caps ) ) {
		foreach ( $caps as $role => $on ) {
			if ( $on ) {
				$found[ $id ]['roles'][] = $site . $role;
			}
		}
	}
}
foreach ( $found as $id => $user ) {
	sort( $user['roles'] );
	$lines[] = array( 'user', $id, $user['login'], $user['email'], $user['roles'] ? implode( ',', $user['roles'] ) : '(権限なし)' );
	if ( $detail ) {
		$lines[] = array( 'user_registered', $id, $user['registered'] . ' (UTC)' );
	}
}

// 有効化されているプラグイン
$option = mysqli_query( $db, "SELECT option_value FROM `{$prefix}options` WHERE option_name = 'active_plugins' LIMIT 1" );
if ( ! $option ) {
	fwrite( STDERR, '有効化プラグインを取得できません: ' . mysqli_error( $db ) . "\n" );
	exit( 2 );
}
$row    = mysqli_fetch_row( $option );
$active = $row ? @unserialize( (string) $row[0], array( 'allowed_classes' => false ) ) : array();
if ( is_array( $active ) ) {
	foreach ( $active as $plugin ) {
		$lines[] = array( 'active_plugin', (string) $plugin );
	}
}

foreach ( $lines as $fields ) {
	echo implode( "\t", array_map( 'clean_field', $fields ) ), "\n";
}
exit( 0 );

/* ---------- helpers ---------- */

/** ディレクトリ直下の名前一覧（隠しファイルを含む。index.php は除く） */
function list_entries( $dir ) {
	$names = array();
	if ( is_dir( $dir ) && ( $handle = opendir( $dir ) ) ) {
		while ( ( $name = readdir( $handle ) ) !== false ) {
			if ( $name !== '.' && $name !== '..' && $name !== 'index.php' ) {
				$names[] = $name;
			}
		}
		closedir( $handle );
	}
	return $names;
}

/** define( 'X', '...' ) と $table_prefix = '...' を実行せずに読み取る */
function parse_wp_config( $source ) {
	$tokens = array();
	foreach ( token_get_all( $source ) as $token ) {
		if ( is_array( $token ) && in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$tokens[] = $token;
	}

	$values = array();
	$count  = count( $tokens );
	for ( $i = 0; $i < $count; $i++ ) {
		$t = $tokens[ $i ];
		if ( ! is_array( $t ) ) {
			continue;
		}
		$is_define = ( $t[0] === T_STRING && strtolower( $t[1] ) === 'define' )
			|| ( defined( 'T_NAME_FULLY_QUALIFIED' ) && $t[0] === T_NAME_FULLY_QUALIFIED && strtolower( $t[1] ) === '\\define' );
		if ( $is_define && $i + 4 < $count
			&& $tokens[ $i + 1 ] === '('
			&& is_string_literal( $tokens[ $i + 2 ] )
			&& $tokens[ $i + 3 ] === ','
			&& is_string_literal( $tokens[ $i + 4 ] ) ) {
			$values[ string_value( $tokens[ $i + 2 ][1] ) ] = string_value( $tokens[ $i + 4 ][1] );
		}
		if ( $t[0] === T_VARIABLE && $t[1] === '$table_prefix' && $i + 2 < $count
			&& $tokens[ $i + 1 ] === '='
			&& is_string_literal( $tokens[ $i + 2 ] ) ) {
			$values['table_prefix'] = string_value( $tokens[ $i + 2 ][1] );
		}
	}
	return $values;
}

function is_string_literal( $token ) {
	return is_array( $token ) && $token[0] === T_CONSTANT_ENCAPSED_STRING;
}

function string_value( $literal ) {
	$body = substr( $literal, 1, -1 );
	if ( $literal[0] === "'" ) {
		return str_replace( array( "\\\\", "\\'" ), array( "\\", "'" ), $body );
	}
	return stripcslashes( $body );
}

/** DB_HOST の 'host' / 'host:3307' / 'localhost:/path/to/mysql.sock' を分解 */
function parse_db_host( $value ) {
	$host   = $value;
	$port   = null;
	$socket = null;
	$colon  = strrpos( $value, ':' );
	if ( $colon !== false && strpos( $value, ']' ) === false ) {
		$host = substr( $value, 0, $colon );
		$rest = substr( $value, $colon + 1 );
		if ( ctype_digit( $rest ) ) {
			$port = (int) $rest;
		} elseif ( $rest !== '' ) {
			$socket = $rest;
		}
	}
	return array( $host === '' ? 'localhost' : $host, $port, $socket );
}

/** タブ・改行・制御文字を空白にする（1行1項目を保つ） */
function clean_field( $value ) {
	return preg_replace( '/[\x00-\x1F\x7F]/', ' ', (string) $value );
}
