"use client";

import { useState } from "react";
import { categories } from "@/lib/mock-shops";

export function CategoryTabs() {
  const [active, setActive] = useState<(typeof categories)[number]["id"]>("all");

  return (
    <nav
      className="flex gap-2 overflow-x-auto border-b border-neutral-200 bg-white px-4 scrollbar-none [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      aria-label="カテゴリ"
    >
      {categories.map((cat) => {
        const isActive = active === cat.id;
        return (
          <button
            key={cat.id}
            type="button"
            onClick={() => setActive(cat.id)}
            className={`relative shrink-0 px-1 py-3 text-sm font-semibold transition-colors ${
              isActive ? "text-brand-orange" : "text-neutral-500"
            }`}
          >
            {cat.label}
            {isActive && (
              <span className="absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-brand-orange" />
            )}
          </button>
        );
      })}
    </nav>
  );
}
