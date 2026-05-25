export function SectionHeading({
  number,
  title,
}: {
  number: string;
  title: string;
}) {
  return (
    <h2 className="mb-4 flex items-center gap-2 text-lg font-bold">
      <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-blue to-brand-blue-dark text-sm font-black text-white shadow-sm">
        {number}
      </span>
      <span className="bg-gradient-to-r from-brand-blue to-brand-blue-dark bg-clip-text text-transparent">
        {title}
      </span>
    </h2>
  );
}
