import { forwardRef, useId, type InputHTMLAttributes } from "react";
import { cn } from "@/utils/cn";

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  hint?: string;
}

/**
 * Component Library, Section 6.2. Error state shows a red border plus
 * inline helper text below the field, exactly as specified — never just
 * a red border with no explanation of what's wrong.
 */
export const Input = forwardRef<HTMLInputElement, InputProps>(function Input(
  { label, error, hint, id, className, ...props },
  ref
) {
  const generatedId = useId();
  const inputId = id ?? generatedId;
  const describedBy = error ? `${inputId}-error` : hint ? `${inputId}-hint` : undefined;

  return (
    <div className="flex flex-col gap-1.5">
      {label && (
        <label htmlFor={inputId} className="text-body-sm font-medium text-[color:var(--color-text)]">
          {label}
        </label>
      )}
      <input
        ref={ref}
        id={inputId}
        aria-invalid={Boolean(error)}
        aria-describedby={describedBy}
        className={cn(
          "h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body",
          "text-[color:var(--color-text)] placeholder:text-neutral-500",
          "focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold focus-visible:outline-offset-2",
          "disabled:cursor-not-allowed disabled:opacity-50",
          error && "border-danger",
          className
        )}
        {...props}
      />
      {error && (
        <p id={`${inputId}-error`} className="text-caption text-danger">
          {error}
        </p>
      )}
      {!error && hint && (
        <p id={`${inputId}-hint`} className="text-caption text-neutral-500">
          {hint}
        </p>
      )}
    </div>
  );
});
