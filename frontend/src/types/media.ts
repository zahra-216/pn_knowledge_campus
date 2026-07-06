/** Matches MediaResource on the backend (Database Design, Section 4.3). */
export interface MediaItem {
  id: number;
  name: string;
  file_name: string;
  mime_type: string | null;
  size: number;
  collection_name: string;
  folder_id: number | null;
  alt_text: string | null;
  url: string;
  thumb_url: string | null;
  web_url: string | null;
  width: number | null;
  height: number | null;
  uploaded_by: number | null;
  created_at: string;
}

/** Matches MediaFolderResource on the backend. */
export interface MediaFolder {
  id: number;
  parent_id: number | null;
  name: string;
  children: MediaFolder[];
  media_count: number | null;
  created_at: string;
}

export type MediaTypeFilter = "image" | "video" | "document";
