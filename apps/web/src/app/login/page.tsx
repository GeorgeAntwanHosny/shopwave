"use client";

import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { loginSchema, type LoginFormValues } from "@/lib/validations/auth";
import { useLogin } from "@/features/auth/hooks/useLogin";
import { ApiError } from "@/lib/api/client";
import Link from "next/link";

export default function LoginPage() {
  const router = useRouter();
  const { mutate, isPending } = useLogin();

  const form = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: "", password: "" },
  });

  function onSubmit(values: LoginFormValues) {
    mutate(values, {
      onSuccess: () => {
        toast.success("Welcome back!");
        router.push("/dashboard");
      },
      onError: (error) => {
        const err = error as ApiError;
        if (err.fieldErrors) {
          Object.entries(err.fieldErrors).forEach(([field, messages]) => {
            form.setError(field as keyof LoginFormValues, { message: messages[0] });
          });
        }
        toast.error(err.message || "Login failed. Please check your credentials.");
      },
    });
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8">
      <div className="w-full max-w-sm space-y-6 rounded-lg border border-border bg-card p-6 shadow-sm sm:p-8">
        <div className="space-y-1 text-center">
          <h1 className="text-2xl font-semibold text-card-foreground">Welcome back</h1>
          <p className="text-sm text-muted-foreground">Log in to your ShopWave account</p>
        </div>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4" noValidate>
            <FormField
              control={form.control}
              name="email"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Email</FormLabel>
                  <FormControl>
                    <Input type="email" placeholder="jane@example.com" autoComplete="email" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="password"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Password</FormLabel>
                  <FormControl>
                    <Input type="password" autoComplete="current-password" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <Button type="submit" className="w-full" disabled={isPending}>
              {isPending ? "Logging in..." : "Login"}
            </Button>
          </form>
        </Form>

        <p className="text-center text-sm text-muted-foreground">
          Don&apos;t have an account?{" "}
          <Link href="/register" className="font-medium text-primary underline-offset-4 hover:underline">
            Register
          </Link>
        </p>
      </div>
    </div>
  );
}