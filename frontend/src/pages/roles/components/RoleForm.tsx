import { useEffect, useState } from "react";
import { Modal, Button, Input } from "@/components/ui";
import type { AdminRole, PermissionsByModule, RolePayload } from "@/types/user";

interface RoleFormProps {
  open: boolean;
  role: AdminRole | null;
  isBaseline: boolean;
  permissionsByModule: PermissionsByModule;
  onClose: () => void;
  onSave: (payload: RolePayload) => Promise<void>;
}

/**
 * SRS FR-29 — "allowing Super Admins to create custom roles". Name is
 * locked for the five baseline roles (RoleController blocks renaming
 * them server-side too; disabling the field here avoids a round-trip
 * 422 for the obvious case). Permissions are always fully editable,
 * baseline or custom.
 */
export function RoleForm({ open, role, isBaseline, permissionsByModule, onClose, onSave }: RoleFormProps) {
  const [name, setName] = useState("");
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setName(role?.name ?? "");
    setSelected(new Set(role?.permissions ?? []));
  }, [role, open]);

  function toggle(permission: string) {
    setSelected((current) => {
      const next = new Set(current);
      if (next.has(permission)) next.delete(permission);
      else next.add(permission);
      return next;
    });
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      const payload: RolePayload = { permissions: Array.from(selected) };
      if (!isBaseline) payload.name = name;
      await onSave(payload);
      onClose();
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={role ? `Edit Role: ${role.name}` : "New Role"}
      size="wide"
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button onClick={handleSave} isLoading={isSaving}>
            Save
          </Button>
        </>
      }
    >
      <div className="flex flex-col gap-4">
        <Input
          label="Role Name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          disabled={isBaseline}
          hint={isBaseline ? "The five baseline roles cannot be renamed." : undefined}
        />

        <div>
          <p className="mb-2 text-body-sm font-medium text-[color:var(--color-text)]">Permissions</p>
          <div className="grid max-h-96 gap-4 overflow-y-auto rounded-sm border border-[color:var(--color-border)] p-4 sm:grid-cols-2 lg:grid-cols-3">
            {Object.entries(permissionsByModule).map(([module, permissions]) => (
              <div key={module} className="flex flex-col gap-1.5">
                <p className="text-caption font-semibold uppercase tracking-wide text-neutral-500">{module}</p>
                {permissions.map((permission) => (
                  <label key={permission} className="flex items-center gap-2 text-body-sm text-[color:var(--color-text)]">
                    <input type="checkbox" checked={selected.has(permission)} onChange={() => toggle(permission)} />
                    {permission.split(".")[1]}
                  </label>
                ))}
              </div>
            ))}
          </div>
        </div>
      </div>
    </Modal>
  );
}
