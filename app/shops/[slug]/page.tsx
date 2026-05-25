import { notFound } from "next/navigation";
import { BasicInfoSection } from "@/app/components/shop/detail/BasicInfoSection";
import { DetailHero } from "@/app/components/shop/detail/DetailHero";
import { DetailPageHeader } from "@/app/components/shop/detail/DetailPageHeader";
import { EditorReviewSection } from "@/app/components/shop/detail/EditorReviewSection";
import { FeaturesSection } from "@/app/components/shop/detail/FeaturesSection";
import { FixedSiteCta } from "@/app/components/shop/detail/FixedSiteCta";
import { TrendsSection } from "@/app/components/shop/detail/TrendsSection";
import { getAllShopSlugs, getShopDetailBySlug } from "@/lib/shop-detail";
import { getShopBySlug } from "@/lib/mock-shops";

type PageProps = {
  params: Promise<{ slug: string }>;
};

export function generateStaticParams() {
  return getAllShopSlugs().map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: PageProps) {
  const { slug } = await params;
  const shop = getShopBySlug(slug);
  if (!shop) return { title: "ショップが見つかりません" };
  return {
    title: `${shop.name} | オリパリスト`,
    description: shop.description,
  };
}

export default async function ShopDetailPage({ params }: PageProps) {
  const { slug } = await params;
  const detail = getShopDetailBySlug(slug);

  if (!detail) {
    notFound();
  }

  return (
    <div className="relative mx-auto min-h-dvh w-full max-w-[430px] bg-neutral-50">
      <DetailPageHeader />
      <main className="pb-[calc(5.5rem+env(safe-area-inset-bottom))]">
        <DetailHero detail={detail} />
        <BasicInfoSection detail={detail} />
        <FeaturesSection detail={detail} />
        <TrendsSection detail={detail} />
        <EditorReviewSection detail={detail} />
      </main>
      <FixedSiteCta url={detail.officialUrl} shopName={detail.name} />
    </div>
  );
}
