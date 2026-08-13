import { useEffect, useState } from "react";
import { Modal, Button, Input, Switch } from "@/components/ui";
import type { AdminUser, UserCreatePayload, UserUpdatePayload } from "@/types/user";

interface UserFormProps {
  open: boolean;
  user: AdminUser | null;
  roleNames: string[];
  isSelf: boolean;
  onClose: () => void;
  onSave: (payload: UserCreatePayload | UserUpdatePayload) => Promise<void>;
}

const EMPTY: UserCreatePayload = { name: "", email: "", password: "", password_confirmation: "", role: "", is_active: true };

/**
 * Password fields are blank-to-keep-unchanged on edit (UserUpdateRequest
 * treats an omitted password as "no change") but required on create
 * (UserCreateRequest). `isSelf` disables the Active toggle — the backend
 * blocks self-deactivation too, but hiding the dead-end here is friendlier
 * than a round-trip 422.
 */
export function UserForm({ open, user, roleNames, isSelf, onClose, onSave }: UserFormProps) {
  const [form, setForm] = useState<UserCreatePayload>(EMPTY);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      user
        ? { name: user.name, email: user.email, password: "", password_confirmation: "", phone: user.phone ?? "", role: user.role ?? "", is_active: user.is_active }
        : { ...EMPTY, role: roleNames[0] ?? "" }
    );
  }, [user, open, roleNames]);

  async function handleSave() {
    setIsSaving(true);
    try {
      const payload = { ...form };
      if (user && !payload.password) {
        delete (payload as Partial<UserCreatePayload>).password;
        delete (payload as Partial<UserCreatePayload>).password_confirmation;
      }
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
      title={user ? "Edit User" : "New User"}
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
        <Input label="Name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
        <Input label="Email" type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
        <Input label="Phone" value={form.phone ?? ""} onChange={(e) => setForm({ ...form, phone: e.target.value })} />

        <Input
          label={user ? "New Password (leave blank to keep current)" : "Password"}
          type="password"
          value={form.password}
          onChange={(e) => setForm({ ...form, password: e.target.value })}
          hint="At least 10 characters, with uppercase, lowercase, a number, and a symbol."
        />
        <Input
          label="Confirm Password"
          type="password"
          value={form.password_confirmation}
          onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
        />

        <div className="flex flex-col gap-1.5">
          <label className="text-body-sm font-medium text-[color:var(--color-text)]">Role</label>
          <select
            value={form.role}
            onChange={(e) => setForm({ ...form, role: e.target.value })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            {roleNames.map((name) => (
              <option key={name} value={name}>
                {name}
              </option>
            ))}
          </select>
        </div>

        <Switch
          label="Active"
          checked={form.is_active ?? true}
          onChange={(checked) => setForm({ ...form, is_active: checked })}
          disabled={isSelf}
        />
      </div>
    </Modal>
  );
}
