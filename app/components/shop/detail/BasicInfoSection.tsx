import type { ShopDetail } from "@/lib/shop-detail";
import { SectionHeading } from "./SectionHeading";

export function BasicInfoSection({ detail }: { detail: ShopDetail }) {
  return (
    <section className="px-4 py-5">
      <SectionHeading number="1" title="基本情報" />
      <div className="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-neutral-100">
        <table className="w-full text-left text-sm">
          <tbody>
            {detail.basicInfo.map((row, index) => {
              const Icon = row.icon;
              return (
                <tr
                  key={row.label}
                  className={
                    index < detail.basicInfo.length - 1
                      ? "border-b border-neutral-100"
                      : ""
                  }
                >
                  <th className="w-10 px-3 py-3.5 align-top">
                    <span className="flex size-8 items-center justify-center rounded-lg bg-brand-orange-light">
                      <Icon className="size-4 text-brand-orange" strokeWidth={2.2} />
                    </span>
                  </th>
                  <td className="px-1 py-3.5 pr-4 align-top">
                    <p className="text-[11px] font-semibold text-neutral-500">
                      {row.label}
                    </p>
                    <p className="mt-0.5 font-bold leading-snug text-foreground">
                      {row.value}
                    </p>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </section>
  );
}
