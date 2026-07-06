/**
 * Shared style hook for every place raw HTML from the project's own
 * lightweight RichTextEditor gets rendered on the public site (Page
 * Builder's `rich_text` block, Blog/News post bodies) — the editor
 * writes plain semantic tags (h2/h3/p/ul/ol/a) with no classes, so
 * this is what actually styles them to match the rest of the page.
 */
export const RICH_TEXT_CLASSNAME =
  "text-body text-[color:var(--color-text)] " +
  "[&_h2]:font-display [&_h2]:text-h2 [&_h2]:font-semibold [&_h2]:mt-6 [&_h2]:mb-3 " +
  "[&_h3]:font-display [&_h3]:text-h3 [&_h3]:font-semibold [&_h3]:mt-5 [&_h3]:mb-2 " +
  "[&_p]:mb-4 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 " +
  "[&_a]:text-navy [&_a]:underline dark:[&_a]:text-gold [&_img]:rounded-lg [&_img]:my-4";
