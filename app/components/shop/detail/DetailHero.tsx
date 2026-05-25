import { BadgeCheck } from "lucide-react";
import type { CheckItem, ShopDetail } from "@/lib/shop-detail";

function CheckCard({ item }: { item: CheckItem }) {
  const Icon = item.icon;
  return (
    <div className="w-[140px] shrink-0 rounded-2xl bg-white p-3 shadow-md ring-1 ring-white/50">
      <div className="mb-2 flex size-9 items-center justify-center rounded-xl bg-brand-blue-light">
        <Icon className="size-5 text-brand-blue" strokeWidth={2.2} />
      </div>
      <p className="text-xs font-bold leading-tight text-foreground">{item.title}</p>
      <p className="mt-1 text-[11px] font-semibold text-brand-orange">{item.status}</p>
    </div>
  );
}

export function DetailHero({ detail }: { detail: ShopDetail }) {
  return (
    <section className="relative overflow-hidden bg-gradient-to-br from-brand-orange via-[#ff9a2e] to-brand-orange-dark px-4 pb-5 pt-4">
      <div
        className="pointer-events-none absolute -right-8 top-0 size-40 rounded-full bg-white/10"
        aria-hidden
      />
      <div
        className="pointer-events-none absolute -bottom-6 left-1/3 size-28 rounded-full bg-white/10"
        aria-hidden
      />

      <div className="relative flex gap-3">
        <div className="flex size-[100px] shrink-0 flex-col items-center justify-center rounded-2xl bg-white p-2 shadow-lg ring-2 ring-white/60">
          <div className="flex size-full items-center justify-center rounded-xl bg-gradient-to-br from-brand-orange-light to-brand-orange/25">
            <span className="text-4xl font-black text-brand-orange">
              {detail.logoLetter}
            </span>
          </div>
          <p className="mt-1.5 w-full truncate text-center text-xs font-bold text-foreground">
            {detail.name}
          </p>
        </div>

        <div className="min-w-0 flex-1 pt-1">
          <div className="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 shadow-md">
            <BadgeCheck className="size-4 shrink-0 text-brand-blue" />
            <span className="text-[11px] font-bold leading-tight text-foreground">
              オリパリスト編集部がチェック！
            </span>
          </div>
          <p className="mt-2 text-xs font-medium leading-relaxed text-white/90">
            安心して利用できるショップか、編集部が独自基準で確認しました
          </p>
        </div>
      </div>

      <div className="relative mt-4 -mx-1 overflow-x-auto px-1 pb-1 scrollbar-none [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div className="flex gap-2.5">
          {detail.checks.map((item) => (
            <CheckCard key={item.title} item={item} />
          ))}
        </div>
      </div>
    </section>
  );
}
