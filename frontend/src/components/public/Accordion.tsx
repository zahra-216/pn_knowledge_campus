import { useState } from "react";
import { ChevronDown } from "lucide-react";
import { cn } from "@/utils/cn";

export interface AccordionEntry {
  id: string | number;
  question: string;
  answer: string;
}

/** FAQ accordion — used by the Page Builder's `faq` block and Course Detail's course-scoped FAQs. */
export function Accordion({ items }: { items: AccordionEntry[] }) {
  const [openId, setOpenId] = useState<string | number | null>(items[0]?.id ?? null);

  return (
    <div className="flex flex-col divide-y divide-[color:var(--color-border)] rounded-lg border border-[color:var(--color-border)]">
      {items.map((item) => {
        const open = openId === item.id;
        return (
          <div key={item.id}>
            <button
              type="button"
              onClick={() => setOpenId(open ? null : item.id)}
              aria-expanded={open}
              className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-body font-semibold text-[color:var(--color-text)] hover:bg-navy/5 dark:hover:bg-white/5"
            >
              {item.question}
              <ChevronDown className={cn("h-4 w-4 flex-shrink-0 transition-transform", open && "rotate-180 text-gold")} />
            </button>
            {open && <div className="px-5 pb-4 text-body-sm text-neutral-500">{item.answer}</div>}
          </div>
        );
      })}
    </div>
  );
}
