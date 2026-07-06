import { useEffect, useId, useRef } from "react";
import { Bold, Italic, Underline, List, ListOrdered, Link2, Unlink, Quote, Undo2, Redo2, Heading2, Heading3 } from "lucide-react";
import { cn } from "@/utils/cn";

interface RichTextEditorProps {
  label?: string;
  hint?: string;
  value: string;
  onChange: (html: string) => void;
  disabled?: boolean;
}

interface ToolbarAction {
  label: string;
  icon: typeof Bold;
  command: string;
  arg?: string;
}

const TOOLBAR: ToolbarAction[] = [
  { label: "Bold", icon: Bold, command: "bold" },
  { label: "Italic", icon: Italic, command: "italic" },
  { label: "Underline", icon: Underline, command: "underline" },
  { label: "Heading 2", icon: Heading2, command: "formatBlock", arg: "h2" },
  { label: "Heading 3", icon: Heading3, command: "formatBlock", arg: "h3" },
  { label: "Bullet List", icon: List, command: "insertUnorderedList" },
  { label: "Numbered List", icon: ListOrdered, command: "insertOrderedList" },
  { label: "Quote", icon: Quote, command: "formatBlock", arg: "blockquote" },
];

/**
 * A small contentEditable + document.execCommand toolbar — this project
 * has no WYSIWYG editor dependency (see Page Builder's rich_text block,
 * which uses a raw HTML textarea instead), and Blog's "Rich Editor"
 * feature was scoped to stay that way: zero new npm dependencies,
 * in-house component. execCommand is deprecated but still broadly
 * supported in every evergreen browser; if it's ever removed, this
 * component is the only place that needs replacing.
 */
export function RichTextEditor({ label, hint, value, onChange, disabled }: RichTextEditorProps) {
  const editorRef = useRef<HTMLDivElement>(null);
  const editorId = useId();

  useEffect(() => {
    if (editorRef.current && editorRef.current.innerHTML !== value) {
      editorRef.current.innerHTML = value || "";
    }
  }, [value]);

  function handleInput() {
    onChange(editorRef.current?.innerHTML ?? "");
  }

  function exec(command: string, arg?: string) {
    if (disabled) return;
    editorRef.current?.focus();
    document.execCommand(command, false, arg);
    handleInput();
  }

  function handleLink() {
    const url = window.prompt("Link URL");
    if (url) exec("createLink", url);
  }

  return (
    <div className="flex flex-col gap-1.5">
      {label && (
        <label htmlFor={editorId} className="text-body-sm font-medium text-[color:var(--color-text)]">
          {label}
        </label>
      )}

      <div className={cn("rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)]", disabled && "opacity-50")}>
        <div className="flex flex-wrap items-center gap-0.5 border-b border-[color:var(--color-border)] p-1">
          {TOOLBAR.map(({ label: btnLabel, icon: Icon, command, arg }) => (
            <button
              key={btnLabel}
              type="button"
              aria-label={btnLabel}
              title={btnLabel}
              disabled={disabled}
              onMouseDown={(e) => e.preventDefault()}
              onClick={() => exec(command, arg)}
              className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:cursor-not-allowed dark:hover:bg-white/5"
            >
              <Icon className="h-4 w-4" aria-hidden="true" />
            </button>
          ))}
          <button
            type="button"
            aria-label="Link"
            title="Link"
            disabled={disabled}
            onMouseDown={(e) => e.preventDefault()}
            onClick={handleLink}
            className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:cursor-not-allowed dark:hover:bg-white/5"
          >
            <Link2 className="h-4 w-4" aria-hidden="true" />
          </button>
          <button
            type="button"
            aria-label="Remove Link"
            title="Remove Link"
            disabled={disabled}
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => exec("unlink")}
            className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:cursor-not-allowed dark:hover:bg-white/5"
          >
            <Unlink className="h-4 w-4" aria-hidden="true" />
          </button>
          <span className="mx-1 h-5 w-px bg-[color:var(--color-border)]" />
          <button
            type="button"
            aria-label="Undo"
            title="Undo"
            disabled={disabled}
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => exec("undo")}
            className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:cursor-not-allowed dark:hover:bg-white/5"
          >
            <Undo2 className="h-4 w-4" aria-hidden="true" />
          </button>
          <button
            type="button"
            aria-label="Redo"
            title="Redo"
            disabled={disabled}
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => exec("redo")}
            className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:cursor-not-allowed dark:hover:bg-white/5"
          >
            <Redo2 className="h-4 w-4" aria-hidden="true" />
          </button>
        </div>

        <div
          ref={editorRef}
          id={editorId}
          contentEditable={!disabled}
          onInput={handleInput}
          suppressContentEditableWarning
          className={cn(
            "prose min-h-[240px] max-w-none px-3 py-2 text-body text-[color:var(--color-text)]",
            "focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold focus-visible:outline-offset-2",
            "[&_h2]:font-display [&_h2]:text-h4 [&_h2]:font-semibold [&_h3]:font-display [&_h3]:text-h5 [&_h3]:font-semibold",
            "[&_blockquote]:border-l-2 [&_blockquote]:border-[color:var(--color-border)] [&_blockquote]:pl-3 [&_blockquote]:italic",
            "[&_a]:text-navy [&_a]:underline [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5"
          )}
        />
      </div>

      {hint && <p className="text-caption text-neutral-500">{hint}</p>}
    </div>
  );
}
