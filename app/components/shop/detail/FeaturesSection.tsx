import type { ShopDetail } from "@/lib/shop-detail";
import { SectionHeading } from "./SectionHeading";

export function FeaturesSection({ detail }: { detail: ShopDetail }) {
  return (
    <section className="px-4 py-5">
      <SectionHeading number="2" title="ショップの機能・サービス" />
      <div className="grid grid-cols-2 gap-2.5">
        {detail.features.map((feature) => {
          const Icon = feature.icon;
          const hasFeature = feature.value === "あり" || feature.value.includes("時間");
          return (
            <div
              key={feature.label}
              className="flex flex-col items-center rounded-2xl bg-white px-3 py-4 text-center shadow-sm ring-1 ring-neutral-100"
            >
              <div
                className={`mb-2 flex size-11 items-center justify-center rounded-xl ${
                  hasFeature ? "bg-brand-blue-light" : "bg-neutral-100"
                }`}
              >
                <Icon
                  className={`size-5 ${hasFeature ? "text-brand-blue" : "text-neutral-400"}`}
                  strokeWidth={2.2}
                />
              </div>
              <p className="text-[11px] font-semibold text-neutral-500">
                {feature.label}
              </p>
              <p
                className={`mt-1 text-sm font-bold ${
                  hasFeature ? "text-brand-orange" : "text-neutral-400"
                }`}
              >
                {feature.value}
              </p>
            </div>
          );
        })}
      </div>
    </section>
  );
}
