import { BottomNav } from "./components/home/BottomNav";
import { CategoryTabs } from "./components/home/CategoryTabs";
import { HomeHeader } from "./components/home/HomeHeader";
import { ShopListSection } from "./components/home/ShopListSection";
import { PromoBanner } from "./components/home/PromoBanner";
import { QuickFilters } from "./components/home/QuickFilters";

export default function Home() {
  return (
    <div className="mx-auto min-h-dvh w-full max-w-[430px] bg-background">
      <HomeHeader />
      <main className="pb-[calc(4.5rem+env(safe-area-inset-bottom))]">
        <QuickFilters />
        <PromoBanner />
        <CategoryTabs />
        <ShopListSection />
      </main>
      <BottomNav />
    </div>
  );
}
