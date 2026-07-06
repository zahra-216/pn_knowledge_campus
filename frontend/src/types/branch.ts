/** Matches BranchResource on the backend (Database Design, Section 4.2). */
export interface Branch {
  id: number;
  name: string;
  address: string;
  city: string;
  phone: string | null;
  email: string | null;
  latitude: number | null;
  longitude: number | null;
  is_head_office: boolean;
  order: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export type BranchPayload = Partial<
  Pick<
    Branch,
    "name" | "address" | "city" | "phone" | "email" | "latitude" | "longitude" | "is_head_office" | "order" | "is_active"
  >
>;
