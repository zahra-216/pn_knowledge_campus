import { useState } from "react";
import { Modal, Button } from "@/components/ui";
import { useMediaLibrary } from "@/hooks/useMediaLibrary";
import { MediaGrid } from "@/pages/media/components/MediaGrid";
import { UploadDropzone } from "@/pages/media/components/UploadDropzone";
import type { MediaItem, MediaTypeFilter } from "@/types/media";

interface MediaPickerModalProps {
  open: boolean;
  onClose: () => void;
  onSelect: (item: MediaItem) => void;
  /** Restrict the browsable/uploadable type — e.g. "image" for a logo picker. */
  type?: MediaTypeFilter;
}

/**
 * Development Roadmap, Milestone 1 — "Media Picker modal (reused by
 * every later module)". First real consumer in this milestone is the
 * Settings screen's Branding tab (logo/favicon selection); Course/Page
 * editors reuse it starting Milestone 2+.
 */
export function MediaPickerModal({ open, onClose, onSelect, type }: MediaPickerModalProps) {
  const [picked, setPicked] = useState<MediaItem | null>(null);
  const { media, isLoading, isUploading, upload } = useMediaLibrary({ type, enabled: open });

  function handleConfirm() {
    if (picked) {
      onSelect(picked);
      setPicked(null);
      onClose();
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="Select Media" size="wide" footer={
      <>
        <Button variant="secondary" onClick={onClose}>Cancel</Button>
        <Button onClick={handleConfirm} disabled={!picked}>Select</Button>
      </>
    }>
      <div className="flex flex-col gap-4">
        <UploadDropzone onUpload={async (file, altText) => { await upload(file, altText, null); }} isUploading={isUploading} />
        <MediaGrid media={media} isLoading={isLoading} selectedId={picked?.id} onSelect={setPicked} />
      </div>
    </Modal>
  );
}
