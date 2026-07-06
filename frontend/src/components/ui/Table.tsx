import type { ReactNode } from "react";
import { Spinner } from "./Spinner";
import { EmptyState } from "./EmptyState";

export interface TableColumn<T> {
  key: string;
  header: string;
  render: (row: T) => ReactNode;
  className?: string;
}

interface TableProps<T> {
  columns: TableColumn<T>[];
  rows: T[];
  rowKey: (row: T) => string | number;
  isLoading?: boolean;
  emptyTitle?: string;
  emptyDescription?: string;
}

/**
 * Component Library, Section 6.3 — Data Table. Body Small (14px) row
 * text per Typography §8.3. Loading and empty states are handled here
 * once, so every list screen (Branches, Social Links, later Courses/
 * News/...) gets identical behavior for free.
 */
export function Table<T>({ columns, rows, rowKey, isLoading, emptyTitle = "Nothing here yet", emptyDescription }: TableProps<T>) {
  if (isLoading) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  if (rows.length === 0) {
    return <EmptyState title={emptyTitle} description={emptyDescription} />;
  }

  return (
    <div className="overflow-x-auto rounded-lg border border-[color:var(--color-border)]">
      <table className="w-full text-left text-body-sm">
        <thead className="bg-[color:var(--color-surface-alt)] text-caption font-medium uppercase tracking-wide text-neutral-500">
          <tr>
            {columns.map((col) => (
              <th key={col.key} className={col.className ?? "px-4 py-3"}>
                {col.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-[color:var(--color-border)]">
          {rows.map((row) => (
            <tr key={rowKey(row)} className="hover:bg-black/[0.02] dark:hover:bg-white/[0.02]">
              {columns.map((col) => (
                <td key={col.key} className={col.className ?? "px-4 py-3"}>
                  {col.render(row)}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
