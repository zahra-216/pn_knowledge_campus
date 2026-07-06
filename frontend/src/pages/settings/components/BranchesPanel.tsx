import { useCallback, useEffect, useState } from "react";
import { Plus, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Button, Table, Badge, Modal, Input, Switch, useToast, type TableColumn } from "@/components/ui";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { Branch, BranchPayload } from "@/types/branch";

const EMPTY_FORM: BranchPayload = { name: "", address: "", city: "", is_active: true, is_head_office: false };

/**
 * UI/UX Design, Admin Sitemap (Section 2.2) — "Campus & Branches" tab.
 * Development Roadmap, Milestone 1: "Branches CRUD table + form."
 */
export function BranchesPanel() {
  const { showToast } = useToast();
  const [branches, setBranches] = useState<Branch[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState<BranchPayload>(EMPTY_FORM);
  const [editingId, setEditingId] = useState<number | null>(null);

  const fetchBranches = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Branch>>(ENDPOINTS.branches.admin());
      setBranches(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchBranches();
  }, [fetchBranches]);

  function openCreate() {
    setForm(EMPTY_FORM);
    setEditingId(null);
    setIsModalOpen(true);
  }

  function openEdit(branch: Branch) {
    setForm(branch);
    setEditingId(branch.id);
    setIsModalOpen(true);
  }

  async function handleSave() {
    try {
      if (editingId) {
        await api.put<ApiResponse<Branch>>(ENDPOINTS.branches.admin(editingId), form);
      } else {
        await api.post<ApiResponse<Branch>>(ENDPOINTS.branches.admin(), form);
      }
      setIsModalOpen(false);
      await fetchBranches();
      showToast("Branch saved.", "success");
    } catch {
      showToast("Could not save this branch.", "error");
    }
  }

  async function handleDelete(id: number) {
    await api.delete(ENDPOINTS.branches.admin(id));
    await fetchBranches();
  }

  const columns: TableColumn<Branch>[] = [
    { key: "name", header: "Name", render: (b) => b.name },
    { key: "city", header: "City", render: (b) => b.city },
    {
      key: "head_office",
      header: "Head Office",
      render: (b) => (b.is_head_office ? <Badge tone="info">Head Office</Badge> : null),
    },
    {
      key: "status",
      header: "Status",
      render: (b) => <Badge tone={b.is_active ? "success" : "neutral"}>{b.is_active ? "Active" : "Inactive"}</Badge>,
    },
    {
      key: "actions",
      header: "",
      render: (b) => (
        <div className="flex gap-2">
          <button type="button" onClick={() => openEdit(b)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          <button type="button" onClick={() => handleDelete(b.id)} aria-label={`Delete ${b.name}`}>
            <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-3">
      <div className="flex items-center justify-between">
        <h3 className="text-h4 font-display font-semibold text-[color:var(--color-text)]">Branches</h3>
        <Button size="sm" onClick={openCreate}>
          <Plus className="h-4 w-4" aria-hidden="true" />
          New Branch
        </Button>
      </div>

      <Table
        columns={columns}
        rows={branches}
        rowKey={(b) => b.id}
        isLoading={isLoading}
        emptyTitle="No branches yet"
        emptyDescription="Add your first campus branch/location."
      />

      <Modal
        open={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingId ? "Edit Branch" : "New Branch"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setIsModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleSave}>Save</Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Input label="Name" value={form.name ?? ""} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
          <Input label="Address" value={form.address ?? ""} onChange={(e) => setForm({ ...form, address: e.target.value })} required />
          <Input label="City" value={form.city ?? ""} onChange={(e) => setForm({ ...form, city: e.target.value })} required />
          <Input label="Phone" value={form.phone ?? ""} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
          <Input label="Email" type="email" value={form.email ?? ""} onChange={(e) => setForm({ ...form, email: e.target.value })} />
          <Switch
            label="Head office"
            checked={Boolean(form.is_head_office)}
            onChange={(checked) => setForm({ ...form, is_head_office: checked })}
          />
          <Switch label="Active" checked={form.is_active ?? true} onChange={(checked) => setForm({ ...form, is_active: checked })} />
        </div>
      </Modal>
    </div>
  );
}
