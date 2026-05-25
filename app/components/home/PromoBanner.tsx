export function PromoBanner() {
  return (
    <section className="px-4 pb-2" aria-label="おすすめ">
      <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-blue to-brand-blue-dark p-5 text-white shadow-md">
        <div className="relative z-10">
          <span className="inline-block rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold">
            本日のピックアップ
          </span>
          <h2 className="mt-2 text-lg font-bold leading-snug">
            人気ショップのオリパを
            <br />
            まとめてチェック
          </h2>
          <p className="mt-1 text-sm text-white/85">
            ショップごとの掲載数・単価帯を比較
          </p>
          <button
            type="button"
            className="mt-4 rounded-full bg-white px-5 py-2 text-sm font-bold text-brand-blue shadow-sm active:scale-[0.98]"
          >
            ランキングを見る
          </button>
        </div>
        <div
          className="pointer-events-none absolute -right-6 -top-6 size-32 rounded-full bg-white/10"
          aria-hidden
        />
        <div
          className="pointer-events-none absolute -bottom-8 right-12 size-24 rounded-full bg-white/10"
          aria-hidden
        />
      </div>
    </section>
  );
}
