import { IconBell, IconSearch } from "./icons";

export function HomeHeader() {
  return (
    <header className="bg-brand-orange px-4 pb-4 pt-[max(0.75rem,env(safe-area-inset-top))]">
      <div className="mb-3 flex items-center justify-between">
        <div className="flex items-center gap-2">
          <div
            className="flex size-9 items-center justify-center rounded-xl bg-white/20 text-lg font-bold text-white"
            aria-hidden
          >
            オ
          </div>
          <div>
            <p className="text-xs font-medium text-white/80">オリパをまとめて探す</p>
            <h1 className="text-lg font-bold tracking-tight text-white">
              オリパリスト
            </h1>
          </div>
        </div>
        <button
          type="button"
          className="flex size-10 items-center justify-center rounded-full bg-white/15 text-white transition-colors active:bg-white/25"
          aria-label="お知らせ"
        >
          <IconBell className="size-5" />
        </button>
      </div>

      <label className="relative block">
        <span className="sr-only">オリパを検索</span>
        <IconSearch className="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-neutral-400" />
        <input
          type="search"
          placeholder="オリパ名・サイト名で検索"
          className="h-11 w-full rounded-xl border-0 bg-white pl-11 pr-4 text-sm text-foreground shadow-sm outline-none ring-2 ring-transparent placeholder:text-neutral-400 focus:ring-brand-blue"
        />
      </label>
    </header>
  );
}
