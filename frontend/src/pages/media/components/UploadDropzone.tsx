import { useRef, useState, type DragEvent } from "react";
import { UploadCloud } from "lucide-react";
import { cn } from "@/utils/cn";
import { Input, Button, Spinner } from "@/components/ui";

interface UploadDropzoneProps {
  onUpload: (file: File, altText: string | null) => Promise<void>;
  isUploading: boolean;
}

const IMAGE_EXTENSIONS = [".jpg", ".jpeg", ".png", ".webp"];

/**
 * Component Library, Section 6.2 — File Upload / Dropzone. Alt text is
 * asked for up front only when the selected file is an image, matching
 * the backend's alt-text-required-for-images rule (SRS Section 7.3).
 */
export function UploadDropzone({ onUpload, isUploading }: UploadDropzoneProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [isDragOver, setIsDragOver] = useState(false);
  const [pendingFile, setPendingFile] = useState<File | null>(null);
  const [altText, setAltText] = useState("");

  const isPendingImage = pendingFile ? IMAGE_EXTENSIONS.some((ext) => pendingFile.name.toLowerCase().endsWith(ext)) : false;

  function selectFile(file: File) {
    setPendingFile(file);
    setAltText("");
  }

  function handleDrop(e: DragEvent<HTMLDivElement>) {
    e.preventDefault();
    setIsDragOver(false);
    const file = e.dataTransfer.files[0];
    if (file) selectFile(file);
  }

  async function confirmUpload() {
    if (!pendingFile) return;
    await onUpload(pendingFile, altText || null);
    setPendingFile(null);
    setAltText("");
    if (inputRef.current) inputRef.current.value = "";
  }

  if (pendingFile) {
    return (
      <div className="flex flex-col gap-3 rounded-lg border border-[color:var(--color-border)] p-4">
        <p className="text-body-sm text-[color:var(--color-text)]">
          Ready to upload: <span className="font-medium">{pendingFile.name}</span>
        </p>
        {isPendingImage && (
          <Input
            label="Alt text"
            hint="Required for images — describes the image for accessibility and SEO."
            value={altText}
            onChange={(e) => setAltText(e.target.value)}
            required
          />
        )}
        <div className="flex gap-2">
          <Button onClick={confirmUpload} isLoading={isUploading} disabled={isPendingImage && !altText.trim()}>
            Upload
          </Button>
          <Button variant="secondary" onClick={() => setPendingFile(null)} disabled={isUploading}>
            Cancel
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div
      onDragOver={(e) => {
        e.preventDefault();
        setIsDragOver(true);
      }}
      onDragLeave={() => setIsDragOver(false)}
      onDrop={handleDrop}
      className={cn(
        "flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-8 text-center transition-colors",
        isDragOver ? "border-gold bg-gold/5" : "border-[color:var(--color-border)]"
      )}
    >
      {isUploading ? (
        <Spinner />
      ) : (
        <>
          <UploadCloud className="h-8 w-8 text-neutral-400" aria-hidden="true" />
          <p className="text-body-sm text-neutral-500">Drag and drop a file, or</p>
          <Button variant="secondary" size="sm" onClick={() => inputRef.current?.click()}>
            Browse Files
          </Button>
        </>
      )}
      <input
        ref={inputRef}
        type="file"
        className="hidden"
        onChange={(e) => {
          const file = e.target.files?.[0];
          if (file) selectFile(file);
        }}
      />
    </div>
  );
}
