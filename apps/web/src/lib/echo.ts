import Echo from "laravel-echo";
import Pusher from "pusher-js";

let echoInstance: Echo<"reverb"> | null = null;
let currentToken: string | null = null;

export function getEcho(token: string | null): Echo<"reverb"> | null {
  if (!token) {
    if (echoInstance) {
      echoInstance.disconnect();
      echoInstance = null;
      currentToken = null;
    }
    return null;
  }

  if (echoInstance && currentToken === token) {
    return echoInstance;
  }

  if (echoInstance) {
    echoInstance.disconnect();
  }

  (window as unknown as { Pusher: typeof Pusher }).Pusher = Pusher;

  echoInstance = new Echo({
    broadcaster: "reverb",
    key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
    wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
    wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 8080),
    wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 8080),
    forceTLS: (process.env.NEXT_PUBLIC_REVERB_SCHEME ?? "http") === "https",
    enabledTransports: ["ws", "wss"],
    authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    },
  });

  const pusher = (echoInstance.connector as unknown as { pusher: Pusher }).pusher;
  pusher.connection.bind("connected", () => console.info("[Echo] Connected to Reverb"));
  pusher.connection.bind("error", (err: unknown) => console.error("[Echo] Connection error", err));

  currentToken = token;
  return echoInstance;
}