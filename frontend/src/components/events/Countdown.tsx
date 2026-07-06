import { useEffect, useState } from "react";

interface CountdownProps {
  target: string;
}

function diffParts(target: string) {
  const diffMs = new Date(target).getTime() - Date.now();

  return {
    diffMs,
    days: Math.floor(diffMs / 86_400_000),
    hours: Math.floor((diffMs / 3_600_000) % 24),
    minutes: Math.floor((diffMs / 60_000) % 60),
    seconds: Math.floor((diffMs / 1000) % 60),
  };
}

/**
 * Live "time until starts_at" display for the admin Events list/editor —
 * this project's React app is the CMS only (no public-facing site exists
 * in this repo), so the countdown renders here rather than on a public
 * event page, as an at-a-glance aid for CMS staff.
 */
export function Countdown({ target }: CountdownProps) {
  const [parts, setParts] = useState(() => diffParts(target));

  useEffect(() => {
    const interval = setInterval(() => setParts(diffParts(target)), 1000);

    return () => clearInterval(interval);
  }, [target]);

  if (parts.diffMs <= 0) {
    return <span className="text-caption text-neutral-500">Started</span>;
  }

  if (parts.days > 0) {
    return (
      <span className="text-caption text-neutral-500">
        Starts in {parts.days}d {parts.hours}h
      </span>
    );
  }

  return (
    <span className="text-caption font-medium text-gold">
      Starts in {String(parts.hours).padStart(2, "0")}:{String(parts.minutes).padStart(2, "0")}:
      {String(parts.seconds).padStart(2, "0")}
    </span>
  );
}
