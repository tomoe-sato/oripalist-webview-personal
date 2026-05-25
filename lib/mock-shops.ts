export type Shop = {
  id: string;
  slug: string;
  name: string;
  description: string;
  oripaCount: number;
  minPrice: number;
  maxPrice: number;
  categories: string[];
  url: string;
  isHot?: boolean;
  isNew?: boolean;
};

export type OripaItem = {
  id: string;
  shopId: string;
  title: string;
  price: number;
  total: number;
  remaining: number;
  isNew?: boolean;
  isHot?: boolean;
};

export const categories = [
  { id: "all", label: "すべて" },
  { id: "pokemon", label: "ポケモン" },
  { id: "onepiece", label: "ワンピース" },
  { id: "yugioh", label: "遊戯王" },
  { id: "other", label: "その他" },
] as const;

export const shops: Shop[] = [
  {
    id: "dopa",
    slug: "dopa",
    name: "DOPA",
    description: "人気ポケカオリパが豊富。高額当たり枠のラインナップが充実しています。",
    oripaCount: 128,
    minPrice: 100,
    maxPrice: 10000,
    categories: ["ポケモン", "ワンピース"],
    url: "https://example.com/dopa",
    isHot: true,
  },
  {
    id: "oripa-do",
    slug: "oripa-do",
    name: "オリパ堂",
    description: "初心者向けの低単価オリパから、狙い目の高単価まで幅広く展開。",
    oripaCount: 86,
    minPrice: 50,
    maxPrice: 5000,
    categories: ["ポケモン", "遊戯王"],
    url: "https://example.com/oripa-do",
    isNew: true,
  },
  {
    id: "clove",
    slug: "clove",
    name: "Clove",
    description: "トレカ専門ショップによる厳選オリパ。鑑定品の当たり枠も多数。",
    oripaCount: 54,
    minPrice: 300,
    maxPrice: 30000,
    categories: ["ポケモン"],
    url: "https://example.com/clove",
    isHot: true,
  },
  {
    id: "toreca-dendo",
    slug: "toreca-dendo",
    name: "トレカの殿堂",
    description: "遊戯王・ポケモンを中心に、コスパ重視のオリパが揃う老舗ショップ。",
    oripaCount: 72,
    minPrice: 100,
    maxPrice: 8000,
    categories: ["遊戯王", "ポケモン", "その他"],
    url: "https://example.com/toreca-dendo",
  },
  {
    id: "orikuji",
    slug: "orikuji",
    name: "オリくじ",
    description: "毎日更新される新着オリパが魅力。SNSキャンペーンも定期開催。",
    oripaCount: 41,
    minPrice: 80,
    maxPrice: 3000,
    categories: ["ポケモン", "ワンピース", "その他"],
    url: "https://example.com/orikuji",
    isNew: true,
  },
];

export const oripaItems: OripaItem[] = [
  {
    id: "1",
    shopId: "dopa",
    title: "PSA10狙い！豪華ポケカオリパ",
    price: 500,
    total: 1000,
    remaining: 342,
    isHot: true,
  },
  {
    id: "2",
    shopId: "oripa-do",
    title: "ルフィ・エース・サボ 3兄弟オリパ",
    price: 300,
    total: 500,
    remaining: 89,
    isNew: true,
  },
  {
    id: "3",
    shopId: "clove",
    title: "25th 記念パック 限定オリパ",
    price: 1000,
    total: 200,
    remaining: 12,
    isHot: true,
  },
  {
    id: "4",
    shopId: "toreca-dendo",
    title: "遊戯王 青眼の白龍チャレンジ",
    price: 200,
    total: 800,
    remaining: 521,
  },
  {
    id: "5",
    shopId: "dopa",
    title: "マリオピカチュウ 激レア枠",
    price: 800,
    total: 300,
    remaining: 0,
  },
  {
    id: "6",
    shopId: "dopa",
    title: "新弾バラエティ 300円オリパ",
    price: 300,
    total: 2000,
    remaining: 1104,
  },
  {
    id: "7",
    shopId: "oripa-do",
    title: "遊戯王 プリズマティックチャレンジ",
    price: 150,
    total: 600,
    remaining: 230,
  },
  {
    id: "8",
    shopId: "clove",
    title: "ピカチュウ vmax 確定枠付き",
    price: 2000,
    total: 100,
    remaining: 7,
    isHot: true,
  },
];

export function getShopById(id: string): Shop | undefined {
  return shops.find((s) => s.id === id);
}

export function getShopBySlug(slug: string): Shop | undefined {
  return shops.find((s) => s.slug === slug);
}

export function getOripaByShopId(shopId: string): OripaItem[] {
  return oripaItems.filter((o) => o.shopId === shopId);
}
