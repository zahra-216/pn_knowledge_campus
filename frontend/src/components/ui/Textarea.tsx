import { forwardRef, useId, type TextareaHTMLAttributes } from "react";
import { cn } from "@/utils/cn";

interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string;
  error?: string;
  hint?: string;
}

/** Component Library, Section 6.2 — same states/usage rules as Input. */
export const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(function Textarea(
  { label, error, hint, id, className, rows = 4, ...props },
  ref
) {
  const generatedId = useId();
  const textareaId = id ?? generatedId;
  const describedBy = error ? `${textareaId}-error` : hint ? `${textareaId}-hint` : undefined;

  return (
    <div className="flex flex-col gap-1.5">
      {label && (
        <label htmlFor={textareaId} className="text-body-sm font-medium text-[color:var(--color-text)]">
          {label}
        </label>
      )}
      <textarea
        ref={ref}
        id={textareaId}
        rows={rows}
        aria-invalid={Boolean(error)}
        aria-describedby={describedBy}
        className={cn(
          "rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 py-2 text-body",
          "text-[color:var(--color-text)] placeholder:text-neutral-500",
          "focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold focus-visible:outline-offset-2",
          "disabled:cursor-not-allowed disabled:opacity-50",
          error && "border-danger",
          className
        )}
        {...props}
      />
      {error && (
        <p id={`${textareaId}-error`} className="text-caption text-danger">
          {error}
        </p>
      )}
      {!error && hint && (
        <p id={`${textareaId}-hint`} className="text-caption text-neutral-500">
          {hint}
        </p>
      )}
    </div>
  );
});
