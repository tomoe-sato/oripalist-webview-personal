import { ExternalLink } from "lucide-react";

export function FixedSiteCta({ url, shopName }: { url: string; shopName: string }) {
  return (
    <div className="fixed bottom-0 left-1/2 z-50 w-full max-w-[430px] -translate-x-1/2 border-t border-neutral-100 bg-white/90 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur-md">
      <a
        href={url}
        target="_blank"
        rel="noopener noreferrer"
        className="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-brand-orange via-[#ff9a2e] to-brand-blue text-base font-bold text-white shadow-lg shadow-brand-orange/25 active:scale-[0.98]"
      >
        公式サイトへ
        <ExternalLink className="size-5" strokeWidth={2.5} />
      </a>
      <p className="mt-2 text-center text-[10px] text-neutral-400">
        {shopName}の公式サイトが開きます
      </p>
    </div>
  );
}
