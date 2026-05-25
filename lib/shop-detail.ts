import type { LucideIcon } from "lucide-react";
import {
  Award,
  Building2,
  CreditCard,
  FileCheck,
  Gift,
  Headphones,
  Layers,
  MessageCircle,
  Package,
  Share2,
  ShieldCheck,
  Sparkles,
  Truck,
  Trophy,
  Zap,
} from "lucide-react";
import { getShopBySlug, shops, type Shop } from "./mock-shops";

export type CheckItem = {
  title: string;
  status: string;
  icon: LucideIcon;
};

export type BasicInfoRow = {
  label: string;
  value: string;
  icon: LucideIcon;
};

export type FeatureItem = {
  label: string;
  value: string;
  icon: LucideIcon;
};

export type RatingItem = {
  label: string;
  score: number;
};

export type ShopDetail = {
  slug: string;
  name: string;
  logoLetter: string;
  officialUrl: string;
  checks: CheckItem[];
  basicInfo: BasicInfoRow[];
  features: FeatureItem[];
  totalReturnRate: string;
  pointReturnRate: string;
  strengths: string[];
  weaknesses: string[];
  recommendedUsers: string[];
  review: {
    overall: number;
    items: RatingItem[];
    comment: string;
  };
};

const defaultChecks: CheckItem[] = [
  { title: "古物商許可", status: "確認済み", icon: ShieldCheck },
  { title: "法人運営", status: "確認済み", icon: Building2 },
  { title: "発送が早い", status: "平均1〜3日", icon: Truck },
  { title: "SNS当選報告", status: "多数あり", icon: MessageCircle },
  { title: "運営歴3年以上", status: "安心の実績", icon: Award },
];

const defaultBasicInfo = (
  tcg: string,
  company: string,
  license: string,
): BasicInfoRow[] => [
  { label: "取り扱いTCG", value: tcg, icon: Layers },
  { label: "支払い方法", value: "クレジットカード / 銀行振込 / コンビニ決済 / PayPay", icon: CreditCard },
  { label: "発送速度", value: "平均1〜3営業日以内に発送", icon: Package },
  { label: "運営会社", value: company, icon: Building2 },
  { label: "古物商許可証", value: license, icon: FileCheck },
];

const defaultFeatures: FeatureItem[] = [
  { label: "初回特典", value: "あり", icon: Gift },
  { label: "ログインボーナス", value: "あり", icon: Sparkles },
  { label: "ランク制度", value: "あり", icon: Trophy },
  { label: "ジャックポット", value: "あり", icon: Zap },
  { label: "招待コード", value: "あり", icon: Share2 },
  { label: "お問い合わせ対応", value: "24時間以内", icon: Headphones },
];

const defaultReview = {
  overall: 4.6,
  items: [
    { label: "還元率", score: 4.5 },
    { label: "初心者向け", score: 4.0 },
    { label: "爆アド期待度", score: 4.5 },
    { label: "演出・ワクワク感", score: 4.5 },
    { label: "ラインナップ", score: 4.5 },
    { label: "引きやすさ", score: 4.0 },
  ],
  comment:
    "高還元イベントが非常に多く、PSA10の封入率も高め！発送も早く安心して利用できます。中〜上級者の爆アド狙いに特におすすめの優良ショップです！",
};

const detailOverrides: Record<string, Partial<ShopDetail>> = {
  dopa: {
    name: "DOPA",
    logoLetter: "D",
    officialUrl: "https://example.com/dopa",
    basicInfo: defaultBasicInfo(
      "ポケモン / ワンピース / 遊戯王 / +2",
      "株式会社ミライラボ",
      "第451380009865号（東京都公安委員会）",
    ),
    totalReturnRate: "平均97.5%",
    pointReturnRate: "1.0%",
    strengths: [
      "高還元イベントが豊富",
      "PSA10の封入率が高い",
      "演出が豪華で楽しい",
      "発送が非常に早い",
    ],
    weaknesses: [
      "人気商品の完売が早い",
      "高額オリパはやや高め",
      "抽選系は少なめ",
    ],
    recommendedUsers: [
      "爆アド狙いの人",
      "中〜上級者",
      "高還元重視の人",
      "演出を楽しみたい人",
    ],
    review: defaultReview,
  },
  "oripa-do": {
    name: "オリパ堂",
    logoLetter: "オ",
    review: {
      overall: 4.3,
      items: [
        { label: "還元率", score: 4.2 },
        { label: "初心者向け", score: 4.8 },
        { label: "爆アド期待度", score: 4.0 },
        { label: "演出・ワクワク感", score: 4.2 },
        { label: "ラインナップ", score: 4.4 },
        { label: "引きやすさ", score: 4.5 },
      ],
      comment:
        "低単価オリパが充実しており、初心者でも始めやすいのが魅力。イベントも定期的に開催され、コスパ重視の方にぴったりです。",
    },
    strengths: ["低単価オリパが豊富", "初心者向けガイド充実", "毎日ログインボーナス", "還元キャンペーン多め"],
    weaknesses: ["超高額帯は少なめ", "人気時間帯は混雑", "演出はややシンプル"],
    recommendedUsers: ["初心者", "少額から楽しみたい人", "コスパ重視"],
  },
  clove: {
    name: "Clove",
    logoLetter: "C",
    totalReturnRate: "平均96.8%",
    review: {
      overall: 4.5,
      items: [
        { label: "還元率", score: 4.4 },
        { label: "初心者向け", score: 3.8 },
        { label: "爆アド期待度", score: 4.7 },
        { label: "演出・ワクワク感", score: 4.3 },
        { label: "ラインナップ", score: 4.6 },
        { label: "引きやすさ", score: 3.9 },
      ],
      comment:
        "鑑定品・高レアリティ枠のラインナップが強み。コレクター志向のユーザーに特におすすめできるショップです。",
    },
    strengths: ["PSA10・鑑定品が豊富", "高額当たりの実績", "ブランド信頼性が高い"],
    weaknesses: ["単価帯はやや高め", "初心者には難易度あり"],
    recommendedUsers: ["コレクター", "高額当たり狙い", "鑑定品好き"],
  },
};

function buildDetailFromShop(shop: Shop): ShopDetail {
  const tcg =
    shop.categories.length >= 3
      ? `${shop.categories.slice(0, 3).join(" / ")} / +${shop.categories.length - 3}`
      : shop.categories.join(" / ");

  const override = detailOverrides[shop.slug] ?? {};

  return {
    slug: shop.slug,
    name: shop.name,
    logoLetter: shop.name.charAt(0),
    officialUrl: shop.url,
    checks: defaultChecks,
    basicInfo: defaultBasicInfo(
      tcg,
      `${shop.name}運営事務局`,
      "各都道府県公安委員会（詳細は公式サイト参照）",
    ),
    features: defaultFeatures,
    totalReturnRate: "平均96.0%",
    pointReturnRate: "0.5〜1.0%",
    strengths: ["ラインナップが豊富", "キャンペーン開催", "会員ランクあり"],
    weaknesses: ["人気商品は完売が早い", "高額帯は在庫限り"],
    recommendedUsers: ["トレカ好き", "イベント参加したい人"],
    review: {
      overall: 4.2,
      items: defaultReview.items,
      comment: shop.description,
    },
    ...override,
  };
}

export function getShopDetailBySlug(slug: string): ShopDetail | undefined {
  const shop = getShopBySlug(slug);
  if (!shop) return undefined;
  return buildDetailFromShop(shop);
}

export function getAllShopSlugs(): string[] {
  return shops.map((s) => s.slug);
}
