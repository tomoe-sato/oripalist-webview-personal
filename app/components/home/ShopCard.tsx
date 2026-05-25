import Link from "next/link";
import type { Shop } from "@/lib/mock-shops";

function formatPrice(n: number) {
  return n.toLocaleString("ja-JP");
}

export function ShopCard({ shop }: { shop: Shop }) {
  return (
    <Link
      href={`/shops/${shop.slug}`}
      className="flex gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-neutral-100 transition-colors active:bg-neutral-50"
    >
      <div className="relative flex size-[72px] shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-orange-light to-brand-orange/30">
        <span className="text-xl font-black text-brand-orange" aria-hidden>
          {shop.name.charAt(0)}
        </span>
        {shop.isHot && (
          <span className="absolute -left-1 -top-1 rounded-md bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold text-white">
            HOT
          </span>
        )}
        {shop.isNew && (
          <span className="absolute -right-1 -top-1 rounded-md bg-brand-blue px-1.5 py-0.5 text-[10px] font-bold text-white">
            NEW
          </span>
        )}
      </div>

      <div className="min-w-0 flex-1">
        <h3 className="text-base font-bold text-foreground">{shop.name}</h3>
        <p className="mt-0.5 line-clamp-2 text-xs leading-relaxed text-neutral-500">
          {shop.description}
        </p>

        <div className="mt-2 flex flex-wrap gap-1">
          {shop.categories.map((cat) => (
            <span
              key={cat}
              className="rounded-md bg-brand-blue-light px-2 py-0.5 text-[10px] font-bold text-brand-blue"
            >
              {cat}
            </span>
          ))}
        </div>

        <div className="mt-2 flex items-center justify-between text-xs text-neutral-500">
          <span>
            掲載{" "}
            <span className="font-bold text-brand-blue">{shop.oripaCount}</span>
            件
          </span>
          <span>
            ¥{formatPrice(shop.minPrice)}〜
            <span className="font-bold text-brand-orange">
              ¥{formatPrice(shop.maxPrice)}
            </span>
          </span>
        </div>
      </div>
    </Link>
  );
}
