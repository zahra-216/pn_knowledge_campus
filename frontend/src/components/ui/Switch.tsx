import { cn } from "@/utils/cn";

interface SwitchProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  label?: string;
  disabled?: boolean;
}

/**
 * Component Library, Section 6.2 — Toggle Switch. Used for boolean flags
 * (is_active, is_featured, requires_inquiry, ...).
 */
export function Switch({ checked, onChange, label, disabled }: SwitchProps) {
  return (
    <label className={cn("inline-flex items-center gap-2", disabled ? "cursor-not-allowed opacity-50" : "cursor-pointer")}>
      <button
        type="button"
        role="switch"
        aria-checked={checked}
        disabled={disabled}
        onClick={() => onChange(!checked)}
        className={cn(
          "relative h-6 w-11 flex-shrink-0 rounded-full transition-colors",
          "focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold focus-visible:outline-offset-2",
          checked ? "bg-navy" : "bg-neutral-300"
        )}
      >
        <span
          className={cn(
            "absolute top-0.5 h-5 w-5 rounded-full bg-white transition-transform",
            checked ? "translate-x-[22px]" : "translate-x-0.5"
          )}
        />
      </button>
      {label && <span className="text-body-sm text-[color:var(--color-text)]">{label}</span>}
    </label>
  );
}
