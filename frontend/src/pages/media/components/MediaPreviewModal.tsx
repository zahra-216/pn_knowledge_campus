import { FileText } from "lucide-react";
import { Modal, Button } from "@/components/ui";
import type { MediaItem } from "@/types/media";

interface MediaPreviewModalProps {
  item: MediaItem | null;
  onClose: () => void;
}

/**
 * Media Library hardening — "File preview". Distinct from
 * MediaDetailPanel (which edits metadata): this is a read-only, full-size
 * look at the asset — image lightbox, video playback, or a link-out for
 * PDFs/documents that browsers don't render inline consistently.
 */
export function MediaPreviewModal({ item, onClose }: MediaPreviewModalProps) {
  if (!item) return null;

  const isImage = item.mime_type?.startsWith("image/");
  const isVideo = item.mime_type?.startsWith("video/");
  const isPdf = item.mime_type === "application/pdf";

  return (
    <Modal open={Boolean(item)} onClose={onClose} title={item.name} size="wide">
      <div className="flex flex-col items-center gap-4">
        {isImage && (
          <img src={item.web_url ?? item.url} alt={item.alt_text ?? ""} className="max-h-[70vh] max-w-full rounded-md object-contain" />
        )}

        {isVideo && <video src={item.url} controls className="max-h-[70vh] max-w-full rounded-md" />}

        {isPdf && (
          <iframe src={item.url} title={item.name} className="h-[70vh] w-full rounded-md border border-[color:var(--color-border)]" />
        )}

        {!isImage && !isVideo && !isPdf && (
          <div className="flex flex-col items-center gap-3 py-12 text-center">
            <FileText className="h-12 w-12 text-neutral-400" aria-hidden="true" />
            <p className="text-body-sm text-neutral-500">Preview isn't available for this file type in the browser.</p>
            <Button variant="secondary" size="sm" onClick={() => window.open(item.url, "_blank", "noopener,noreferrer")}>
              Open File
            </Button>
          </div>
        )}

        {item.width && item.height && (
          <p className="text-caption text-neutral-500">
            {item.width} × {item.height}px
          </p>
        )}
      </div>
    </Modal>
  );
}
