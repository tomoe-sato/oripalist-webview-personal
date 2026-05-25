import { IconCompass, IconHeart, IconHome, IconUser } from "./icons";

const tabs = [
  { id: "home", label: "ホーム", icon: IconHome, active: true },
  { id: "discover", label: "探す", icon: IconCompass, active: false },
  { id: "favorite", label: "お気に入り", icon: IconHeart, active: false },
  { id: "mypage", label: "マイページ", icon: IconUser, active: false },
] as const;

export function BottomNav() {
  return (
    <nav
      className="fixed bottom-0 left-1/2 z-50 w-full max-w-[430px] -translate-x-1/2 border-t border-neutral-200 bg-white pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-1"
      aria-label="メインメニュー"
    >
      <ul className="flex">
        {tabs.map((tab) => {
          const Icon = tab.icon;
          return (
            <li key={tab.id} className="flex-1">
              <button
                type="button"
                className={`flex w-full flex-col items-center gap-0.5 py-2 text-[10px] font-semibold ${
                  tab.active ? "text-brand-orange" : "text-neutral-400"
                }`}
                aria-current={tab.active ? "page" : undefined}
              >
                <Icon className={`size-6 ${tab.active ? "text-brand-orange" : ""}`} />
                {tab.label}
              </button>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
