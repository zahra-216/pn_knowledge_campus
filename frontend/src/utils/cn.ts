import { clsx, type ClassValue } from "clsx";

/**
 * Tiny wrapper so every component imports `cn` from one place instead of
 * `clsx` directly — if a class-merging strategy is ever swapped in
 * (e.g. tailwind-merge added later to dedupe conflicting utility
 * classes), this is the only file that changes.
 */
export function cn(...inputs: ClassValue[]): string {
  return clsx(...inputs);
}
