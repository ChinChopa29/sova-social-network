// import Echo from "laravel-echo";
// import io from "socket.io-client";

// window.io = io;

// window.Echo = new Echo({
//   broadcaster: "socket.io",
//   host: "http://localhost:6001",
//   client: io,
//   transports: ["websocket"],
//   forceBase64: false,
//   withCredentials: true,
//   enabledTransports: ["ws", "wss"],
//   auth: {
//     headers: {
//       Authorization: `Bearer ${localStorage.getItem("token")}`,
//       "X-CSRF-TOKEN":
//         document.querySelector('meta[name="csrf-token"]')?.content || "",
//     },
//   },
// });

// window.Echo.connector.socket.on("connect", () => {
//   console.log("✅ Connected to Echo Server");
// });

// window.Echo.connector.socket.on("error", (error) => {
//   console.error("Echo Server error:", error);
// });
