/** Matches MenuItemResource on the backend. */
export interface MenuItem {
  id: number;
  parent_id: number | null;
  label: string;
  linkable_type: string | null;
  linkable_id: number | null;
  custom_url: string | null;
  url: string | null;
  description: string | null;
  icon: string | null;
  is_mega_menu: boolean;
  open_in_new_tab: boolean;
  order: number;
  is_active: boolean;
  starts_at: string | null;
  ends_at: string | null;
  visible_on: "both" | "desktop" | "mobile";
  children: MenuItem[];
}

/** Matches MenuResource on the backend. */
export interface Menu {
  id: number;
  name: string;
  items: MenuItem[];
  created_at: string;
}

/** The lightweight shape returned by the admin menus list endpoint. */
export interface MenuSummary {
  id: number;
  name: string;
  items_count: number;
  created_at: string;
}

export type MenuItemPayload = Partial<
  Pick<
    MenuItem,
    | "parent_id"
    | "label"
    | "linkable_type"
    | "linkable_id"
    | "custom_url"
    | "description"
    | "icon"
    | "is_mega_menu"
    | "open_in_new_tab"
    | "order"
    | "is_active"
    | "starts_at"
    | "ends_at"
    | "visible_on"
  >
>;
