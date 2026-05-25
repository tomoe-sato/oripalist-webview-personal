import { shops } from "@/lib/mock-shops";
import { ShopCard } from "./ShopCard";

export function ShopListSection() {
  return (
    <section className="px-4 pb-4">
      <div className="mb-3 flex items-center justify-between">
        <h2 className="text-base font-bold text-foreground">ショップ一覧</h2>
        <button
          type="button"
          className="text-sm font-semibold text-brand-blue active:opacity-70"
        >
          並び替え
        </button>
      </div>
      <ul className="flex flex-col gap-3">
        {shops.map((shop) => (
          <li key={shop.id}>
            <ShopCard shop={shop} />
          </li>
        ))}
      </ul>
    </section>
  );
}
