import { Star } from "lucide-react";
import type { ShopDetail } from "@/lib/shop-detail";
import { SectionHeading } from "./SectionHeading";

function StarRating({ score, size = "md" }: { score: number; size?: "sm" | "md" }) {
  const iconSize = size === "sm" ? "size-3.5" : "size-5";
  return (
    <div className="flex gap-0.5" aria-label={`${score}点`}>
      {Array.from({ length: 5 }).map((_, i) => {
        const filled = i < Math.floor(score);
        const half = !filled && i < score;
        return (
          <Star
            key={i}
            className={`${iconSize} ${
              filled
                ? "fill-amber-400 text-amber-400"
                : half
                  ? "fill-amber-200 text-amber-400"
                  : "fill-neutral-100 text-neutral-200"
            }`}
          />
        );
      })}
    </div>
  );
}

export function EditorReviewSection({ detail }: { detail: ShopDetail }) {
  const { review } = detail;

  return (
    <section className="px-4 py-5">
      <SectionHeading number="4" title="編集部レビュー" />

      <div className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-neutral-100">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs font-semibold text-neutral-500">総合評価</p>
            <p className="text-4xl font-black text-brand-orange">
              {review.overall.toFixed(1)}
            </p>
          </div>
          <StarRating score={review.overall} />
        </div>

        <ul className="mt-5 flex flex-col gap-3 border-t border-neutral-100 pt-4">
          {review.items.map((item) => (
            <li key={item.label} className="flex items-center justify-between gap-3">
              <span className="shrink-0 text-sm font-semibold text-neutral-600">
                {item.label}
              </span>
              <div className="flex items-center gap-2">
                <StarRating score={item.score} size="sm" />
                <span className="w-7 text-right text-sm font-bold text-brand-blue">
                  {item.score.toFixed(1)}
                </span>
              </div>
            </li>
          ))}
        </ul>

        <div className="relative mt-5 overflow-hidden rounded-2xl bg-gradient-to-br from-brand-orange-light via-white to-brand-blue-light p-4 ring-1 ring-brand-orange/15">
          <div
            className="pointer-events-none absolute -right-4 -top-4 size-16 rounded-full bg-brand-orange/10"
            aria-hidden
          />
          <p className="mb-1 text-xs font-bold text-brand-orange">編集部コメント</p>
          <p className="relative text-sm font-medium leading-relaxed text-foreground">
            {review.comment}
          </p>
        </div>
      </div>
    </section>
  );
}
