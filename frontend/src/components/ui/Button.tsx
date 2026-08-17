import { forwardRef, type ButtonHTMLAttributes } from "react";
import { Loader2 } from "lucide-react";
import { cn } from "@/utils/cn";

export type ButtonVariant = "primary" | "secondary" | "ghost" | "danger";
export type ButtonSize = "sm" | "md";

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  isLoading?: boolean;
}

/**
 * Component Library, Section 6.1. One component, four variants — never
 * a one-off styled <button> anywhere else in the app. States (hover,
 * active, disabled, loading) are all handled here so every button in
 * the product behaves identically.
 */
export const Button = forwardRef<HTMLButtonElement, ButtonProps>(function Button(
  { variant = "primary", size = "md", isLoading = false, disabled, className, children, ...props },
  ref
) {
  return (
    <button
      ref={ref}
      disabled={disabled || isLoading}
      className={cn(
        "inline-flex items-center justify-center gap-2 rounded font-sans font-semibold transition-colors duration-150",
        "focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold focus-visible:outline-offset-2",
        "disabled:cursor-not-allowed disabled:opacity-50",
        size === "md" ? "h-10 px-4 text-body-sm" : "h-8 px-3 text-caption",
        variant === "primary" && "bg-navy text-white hover:bg-navy-light active:bg-navy-dark",
        variant === "secondary" &&
          "border border-navy text-navy hover:bg-navy/5 active:bg-navy/10 dark:border-white/25 dark:text-white dark:hover:bg-white/10 dark:active:bg-white/15",
        variant === "ghost" && "text-navy hover:bg-navy/5 active:bg-navy/10 dark:text-white dark:hover:bg-white/10 dark:active:bg-white/15",
        variant === "danger" && "bg-danger text-white hover:opacity-90 active:opacity-80",
        className
      )}
      {...props}
    >
      {isLoading && <Loader2 className="h-4 w-4 animate-spin" aria-hidden="true" />}
      {children}
    </button>
  );
});