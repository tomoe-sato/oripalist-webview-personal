"use client";

import Link from "next/link";
import { ChevronLeft, Heart } from "lucide-react";
import { useState } from "react";

export function DetailPageHeader() {
  const [favorited, setFavorited] = useState(false);

  return (
    <header className="sticky top-0 z-50 border-b border-neutral-100 bg-white/95 px-2 py-2 backdrop-blur-sm pt-[max(0.25rem,env(safe-area-inset-top))]">
      <div className="flex items-center justify-between">
        <Link
          href="/"
          className="flex size-10 items-center justify-center rounded-full text-neutral-700 transition-colors active:bg-neutral-100"
          aria-label="戻る"
        >
          <ChevronLeft className="size-6" strokeWidth={2.5} />
        </Link>

        <div className="flex items-center gap-1.5">
          <span
            className="flex size-7 items-center justify-center rounded-lg bg-brand-orange text-xs font-black text-white shadow-sm"
            aria-hidden
          >
            オ
          </span>
          <span className="bg-gradient-to-r from-brand-orange to-brand-orange-dark bg-clip-text text-base font-black tracking-tight text-transparent">
            オリパリスト
          </span>
        </div>

        <button
          type="button"
          onClick={() => setFavorited((v) => !v)}
          className="flex size-10 items-center justify-center rounded-full transition-colors active:bg-neutral-100"
          aria-label={favorited ? "お気に入り解除" : "お気に入りに追加"}
          aria-pressed={favorited}
        >
          <Heart
            className={`size-6 transition-colors ${
              favorited
                ? "fill-brand-orange text-brand-orange"
                : "text-neutral-400"
            }`}
          />
        </button>
      </div>
    </header>
  );
}
