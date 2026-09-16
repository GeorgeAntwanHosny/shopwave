import Echo from "laravel-echo";
import Pusher from "pusher-js";

let echoInstance: Echo<"reverb"> | null = null;
let currentToken: string | null = null;

/**
 * Returns a singleton Echo instance, recreated whenever the auth token
 * changes (login/logout).
 */
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

  const config = {
    key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
    wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
    wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 8080),
    scheme: process.env.NEXT_PUBLIC_REVERB_SCHEME ?? "http",
  };

  // TEMPORARY — prints exactly what Echo is about to connect with. If
  // wsHost here isn't "localhost", the problem is upstream of this file
  // (the env var isn't reaching the browser at all) rather than in how
  // Echo is configured. Remove once the connection is confirmed working.
  console.log("[Echo] connecting with config:", config);

  echoInstance = new Echo({
    broadcaster: "reverb",
    key: config.key,
    wsHost: config.wsHost,
    wsPort: config.wsPort,
    wssPort: config.wsPort,
    forceTLS: false,
    enabledTransports: ["ws"],
    authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    },
  });

  const pusher = (echoInstance.connector as unknown as { pusher: Pusher }).pusher;
  pusher.connection.bind("connected", () => {
    console.info("[Echo] Connected to Reverb");
  });
  pusher.connection.bind("error", (err: unknown) => {
    console.error("[Echo] Connection error", err);
  });

  currentToken = token;
  return echoInstance;
}