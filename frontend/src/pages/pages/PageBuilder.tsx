import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Plus, Lock, Pencil, UploadCloud } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Spinner, EmptyState, Badge, useToast } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import { PageBlockList } from "./components/PageBlockList";
import { PageBlockForm } from "./components/PageBlockForm";
import { PageForm } from "./components/PageForm";
import type { ApiResponse } from "@/types/api";
import type { Page, PageBlock, PageBlockPayload, PageStatus } from "@/types/page";

const STATUS_TONE: Record<PageStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * The Page Builder screen for a single page — an ordered, flat list of
 * content blocks (Database Design, Section 4.5; SRS FR-19). UI/UX
 * Design's Page Builder pattern, adapted from MenuBuilder's persist-
 * after-every-mutation approach.
 */
export function PageBuilder() {
  const { id } = useParams<{ id: string }>();
  const pageId = Number(id);
  const { can } = usePermission();
  const { showToast } = useToast();
  const canEdit = can("pages.edit");
  const canPublish = can("pages.publish");

  const [page, setPage] = useState<Page | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isPageFormOpen, setIsPageFormOpen] = useState(false);
  const [blockFormState, setBlockFormState] = useState<{ open: boolean; block: PageBlock | null }>({ open: false, block: null });

  const fetchPage = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<Page>>(ENDPOINTS.pages.admin(pageId));
      setPage(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [pageId]);

  useEffect(() => {
    if (!can("pages.view")) return;
    fetchPage();
  }, [fetchPage, can]);

  async function persistOrder(next: PageBlock[]) {
    if (!page) return;
    setPage({ ...page, blocks: next });
    try {
      await api.patch(ENDPOINTS.pages.reorderBlocks(pageId), {
        items: next.map((b, index) => ({ id: b.id, order: index })),
      });
    } catch {
      showToast("Could not save the new order.", "error");
      fetchPage();
    }
  }

  function handleMove(blockId: number, direction: "up" | "down") {
    if (!page) return;
    const index = page.blocks.findIndex((b) => b.id === blockId);
    const swapWith = direction === "up" ? index - 1 : index + 1;
    if (swapWith < 0 || swapWith >= page.blocks.length) return;

    const next = [...page.blocks];
    [next[index], next[swapWith]] = [next[swapWith], next[index]];
    persistOrder(next);
  }

  function handleDrop(draggedId: number, targetId: number) {
    if (!page || draggedId === targetId) return;
    const next = [...page.blocks];
    const from = next.findIndex((b) => b.id === draggedId);
    const to = next.findIndex((b) => b.id === targetId);
    const [moved] = next.splice(from, 1);
    next.splice(to, 0, moved);
    persistOrder(next);
  }

  async function handleSaveBlock(payload: PageBlockPayload) {
    try {
      if (blockFormState.block) {
        await api.put(ENDPOINTS.pages.blocks(pageId, blockFormState.block.id), payload);
      } else {
        await api.post(ENDPOINTS.pages.blocks(pageId), payload);
      }
      showToast("Block saved.", "success");
      await fetchPage();
    } catch {
      showToast("Could not save this block. Check the required fields for its type.", "error");
    }
  }

  async function handleDeleteBlock(block: PageBlock) {
    await api.delete(ENDPOINTS.pages.blocks(pageId, block.id));
    await fetchPage();
  }

  async function handlePublish() {
    await api.patch(ENDPOINTS.pages.publish(pageId));
    await fetchPage();
  }

  if (!can("pages.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Pages", to: "/admin/pages" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to the Page Builder.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !page) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Pages", to: "/admin/pages" }, { label: page.title }]} />

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{page.title}</h1>
          <Badge tone={STATUS_TONE[page.status]}>{page.status}</Badge>
          <span className="text-body-sm text-neutral-500">/{page.slug}</span>
        </div>
        <div className="flex gap-2">
          {canEdit && (
            <Button variant="secondary" onClick={() => setIsPageFormOpen(true)}>
              <Pencil className="h-4 w-4" aria-hidden="true" />
              Edit Page
            </Button>
          )}
          {canPublish && page.status !== "published" && (
            <Button variant="secondary" onClick={handlePublish}>
              <UploadCloud className="h-4 w-4" aria-hidden="true" />
              Publish
            </Button>
          )}
          {canEdit && (
            <Button onClick={() => setBlockFormState({ open: true, block: null })}>
              <Plus className="h-4 w-4" aria-hidden="true" />
              Add Block
            </Button>
          )}
        </div>
      </div>

      <Card>
        {page.blocks.length === 0 ? (
          <EmptyState title="No blocks yet" description="Add your first content block (Hero, Text, FAQ, ...) to build this page." />
        ) : (
          <PageBlockList
            blocks={page.blocks}
            onEdit={(block) => setBlockFormState({ open: true, block })}
            onDelete={handleDeleteBlock}
            onMove={handleMove}
            onDrop={handleDrop}
          />
        )}
      </Card>

      <PageBlockForm
        open={blockFormState.open}
        block={blockFormState.block}
        onClose={() => setBlockFormState({ open: false, block: null })}
        onSave={handleSaveBlock}
      />

      <PageForm
        open={isPageFormOpen}
        page={page}
        onClose={() => setIsPageFormOpen(false)}
        onSaved={async () => {
          setIsPageFormOpen(false);
          await fetchPage();
        }}
      />
    </div>
  );
}
