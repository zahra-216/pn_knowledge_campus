import { useState, type FormEvent } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { GraduationCap } from "lucide-react";
import { useAuth } from "@/context/AuthContext";
import { useToast } from "@/components/ui";
import { Button, Input, Card } from "@/components/ui";
import type { ApiError } from "@/types/api";

/**
 * Component Library, Section 6.2. The only unauthenticated screen in
 * the admin panel. On success, redirects back to wherever the user was
 * trying to go (see ProtectedRoute), or /admin by default.
 */
export function Login() {
  const { login } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const location = useLocation();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const redirectTo = (location.state as { from?: string } | null)?.from ?? "/admin";

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setFieldErrors({});
    setIsSubmitting(true);

    try {
      await login({ email, password, device_name: "CMS Web Login" });
      navigate(redirectTo, { replace: true });
    } catch (err) {
      const apiError = err as ApiError;
      if (apiError.errors) {
        setFieldErrors(apiError.errors);
      } else {
        showToast(apiError.message, "error");
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-navy-dark px-4">
      <Card className="w-full max-w-sm">
        <div className="mb-6 flex flex-col items-center gap-2 text-center">
          <GraduationCap className="h-8 w-8 text-navy" aria-hidden="true" />
          <h1 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">PN Knowledge Campus</h1>
          <p className="text-body-sm text-neutral-500">Sign in to the admin panel</p>
        </div>

        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
          <Input
            label="Email"
            type="email"
            autoComplete="username"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            error={fieldErrors.email?.[0]}
          />
          <Input
            label="Password"
            type="password"
            autoComplete="current-password"
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            error={fieldErrors.password?.[0]}
          />
          <Button type="submit" isLoading={isSubmitting} className="mt-2 w-full">
            Sign in
          </Button>
        </form>
      </Card>
    </div>
  );
}
