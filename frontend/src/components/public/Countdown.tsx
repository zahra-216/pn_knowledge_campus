import { useEffect, useState } from "react";

function diffParts(target: number) {
  const ms = Math.max(0, target - Date.now());
  return {
    days: Math.floor(ms / 86_400_000),
    hours: Math.floor((ms / 3_600_000) % 24),
    minutes: Math.floor((ms / 60_000) % 60),
    seconds: Math.floor((ms / 1000) % 60),
  };
}

/** Live countdown to an event's start time — only rendered for upcoming events. */
export function Countdown({ startsAt }: { startsAt: string }) {
  const target = new Date(startsAt).getTime();
  const [parts, setParts] = useState(() => diffParts(target));

  useEffect(() => {
    const timer = setInterval(() => setParts(diffParts(target)), 1000);
    return () => clearInterval(timer);
  }, [target]);

  if (target <= Date.now()) return null;

  return (
    <div className="flex gap-4">
      {([
        ["Days", parts.days],
        ["Hours", parts.hours],
        ["Minutes", parts.minutes],
        ["Seconds", parts.seconds],
      ] as const).map(([label, value]) => (
        <div key={label} className="flex flex-col items-center">
          <span className="font-display text-h2 font-semibold text-white">{String(value).padStart(2, "0")}</span>
          <span className="text-caption uppercase tracking-wide text-white/70">{label}</span>
        </div>
      ))}
    </div>
  );
}
