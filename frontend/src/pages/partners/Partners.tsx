import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { PartnerForm } from "./components/PartnerForm";
import type { ApiCollection } from "@/types/api";
import type { Partner, PartnerPayload } from "@/types/homepage";

/**
 * SRS Permission Matrix, "Partners" row — Super Admin/Administrator =
 * Full; Marketing = Create/Edit; Content Editor/Admissions = no access.
 */
export function Partners() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("partners.create");
  const canDelete = can("partners.delete");

  const [partners, setPartners] = useState<Partner[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; partner: Partner | null }>({ open: false, partner: null });

  const fetchPartners = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Partner>>(ENDPOINTS.partners.admin(), { params: { per_page: 100 } });
      setPartners(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("partners.view")) return;
    fetchPartners();
  }, [fetchPartners, can]);

  async function handleSave(payload: PartnerPayload) {
    try {
      if (formState.partner) {
        await api.put(ENDPOINTS.partners.admin(formState.partner.id), payload);
      } else {
        await api.post(ENDPOINTS.partners.admin(), payload);
      }
      showToast("Partner saved.", "success");
      await fetchPartners();
    } catch {
      showToast("Could not save this partner.", "error");
    }
  }

  async function handleDelete(partner: Partner) {
    await api.delete(ENDPOINTS.partners.admin(partner.id));
    await fetchPartners();
  }

  if (!can("partners.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Partners" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Partners.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Partner>[] = [
    {
      key: "logo",
      header: "",
      render: (p) =>
        p.logo_url ? (
          <img src={p.logo_url} alt="" className="h-10 w-10 rounded-sm object-contain" />
        ) : (
          <div className="h-10 w-10 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "name", header: "Name", render: (p) => p.name },
    { key: "url", header: "URL", render: (p) => p.url ?? "—" },
    { key: "category", header: "Category", render: (p) => p.category?.name ?? "—" },
    { key: "order", header: "Order", render: (p) => p.order },
    { key: "status", header: "Status", render: (p) => <Badge tone={p.is_active ? "success" : "neutral"}>{p.is_active ? "Active" : "Inactive"}</Badge> },
    {
      key: "actions",
      header: "",
      render: (p) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => setFormState({ open: true, partner: p })} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(p)} aria-label={`Delete ${p.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Partners" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Partners</h1>
        {canCreate && (
          <Button onClick={() => setFormState({ open: true, partner: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Partner
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={partners}
          rowKey={(p) => p.id}
          isLoading={isLoading}
          emptyTitle="No partners yet"
          emptyDescription="Add your first accreditation or partner organization."
        />
      </Card>

      <PartnerForm open={formState.open} partner={formState.partner} onClose={() => setFormState({ open: false, partner: null })} onSave={handleSave} />
    </div>
  );
}
