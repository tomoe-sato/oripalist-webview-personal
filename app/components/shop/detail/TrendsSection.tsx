import { MinusCircle, PlusCircle, Target, Users } from "lucide-react";
import type { ShopDetail } from "@/lib/shop-detail";
import { SectionHeading } from "./SectionHeading";

export function TrendsSection({ detail }: { detail: ShopDetail }) {
  return (
    <section className="px-4 py-5">
      <SectionHeading number="3" title="ショップの傾向" />

      <div className="mb-3 grid grid-cols-2 gap-2.5">
        <div className="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-neutral-100">
          <p className="text-[11px] font-semibold text-neutral-500">総還元率の傾向</p>
          <p className="mt-1 text-xl font-black text-brand-orange">
            {detail.totalReturnRate}
          </p>
        </div>
        <div className="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-neutral-100">
          <p className="text-[11px] font-semibold text-neutral-500">ポイント還元率</p>
          <p className="mt-1 text-xl font-black text-brand-blue">
            {detail.pointReturnRate}
          </p>
        </div>
      </div>

      <div className="mb-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-neutral-100">
        <div className="mb-2.5 flex items-center gap-2">
          <PlusCircle className="size-5 text-brand-blue" />
          <h3 className="text-sm font-bold text-brand-blue">強み</h3>
        </div>
        <ul className="flex flex-col gap-2">
          {detail.strengths.map((text) => (
            <li
              key={text}
              className="rounded-xl bg-brand-blue-light px-3 py-2 text-sm font-semibold text-foreground"
            >
              {text}
            </li>
          ))}
        </ul>
      </div>

      <div className="mb-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-neutral-100">
        <div className="mb-2.5 flex items-center gap-2">
          <MinusCircle className="size-5 text-neutral-400" />
          <h3 className="text-sm font-bold text-neutral-500">弱み</h3>
        </div>
        <ul className="flex flex-col gap-2">
          {detail.weaknesses.map((text) => (
            <li
              key={text}
              className="rounded-xl bg-neutral-50 px-3 py-2 text-sm font-medium text-neutral-600"
            >
              {text}
            </li>
          ))}
        </ul>
      </div>

      <div className="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-neutral-100">
        <div className="mb-2.5 flex items-center gap-2">
          <Users className="size-5 text-brand-orange" />
          <h3 className="text-sm font-bold text-brand-orange">おすすめユーザー層</h3>
        </div>
        <div className="flex flex-wrap gap-2">
          {detail.recommendedUsers.map((tag) => (
            <span
              key={tag}
              className="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-brand-orange-light to-brand-blue-light px-3 py-1.5 text-xs font-bold text-foreground ring-1 ring-brand-orange/20"
            >
              <Target className="size-3 text-brand-blue" />
              {tag}
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}
