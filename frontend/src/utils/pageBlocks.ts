import type { Page, BlockType } from "@/types/page";

/**
 * Typed lookup of one block's `data` by type, for hand-coded pages that
 * still read their copy from the Page Builder's existing content (so an
 * admin keeps edit access) without going through the generic, one-size
 * block-type-switch renderer those pages are deliberately moving away
 * from. Returns undefined if the page hasn't loaded yet, has no block
 * of that type, or that block was toggled inactive by an editor.
 */
export function findBlock<T = Record<string, unknown>>(page: Page | null | undefined, type: BlockType): T | undefined {
  return page?.blocks.find((b) => b.block_type === type && b.is_active)?.data as T | undefined;
}
