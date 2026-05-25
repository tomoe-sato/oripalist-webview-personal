import { IconFire, IconSparkle } from "./icons";

const filters = [
  { id: "hot", label: "人気", icon: IconFire, active: true },
  { id: "new", label: "新着", icon: IconSparkle, active: false },
  { id: "low", label: "低単価", active: false },
  { id: "ending", label: "残りわずか", active: false },
] as const;

export function QuickFilters() {
  return (
    <div className="flex gap-2 overflow-x-auto px-4 py-3 scrollbar-none [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
      {filters.map((filter) => {
        const Icon = "icon" in filter ? filter.icon : null;
        const isActive = filter.active;

        return (
          <button
            key={filter.id}
            type="button"
            className={`flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition-colors ${
              isActive
                ? "bg-brand-blue text-white shadow-sm"
                : "bg-white text-neutral-600 ring-1 ring-neutral-200 active:bg-neutral-50"
            }`}
          >
            {Icon && <Icon className="size-4" />}
            {filter.label}
          </button>
        );
      })}
    </div>
  );
}
