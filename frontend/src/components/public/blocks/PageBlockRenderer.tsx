import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { Accordion } from "@/components/public/Accordion";
import { SmartLink } from "@/components/public/SmartLink";
import { cn } from "@/utils/cn";
import { RICH_TEXT_CLASSNAME } from "@/utils/richText";
import type {
  ChairmanMessageBlockData,
  CtaBlockData,
  DirectorItem,
  FaqItem,
  GalleryBlockData,
  HeroBlockData,
  ImageBlockData,
  PageBlock,
  PartnerItem,
  StatisticItem,
  TestimonialItem,
  VideoBlockData,
} from "@/types/page";

function collectMediaIds(blocks: PageBlock[]): number[] {
  const ids: number[] = [];
  for (const block of blocks) {
    const d = block.data;
    if (typeof d.media_id === "number") ids.push(d.media_id);
    if (Array.isArray(d.media_ids)) ids.push(...(d.media_ids as number[]));
    if (Array.isArray(d.items)) {
      for (const item of d.items as Record<string, unknown>[]) {
        if (typeof item.avatar_media_id === "number") ids.push(item.avatar_media_id);
        if (typeof item.logo_media_id === "number") ids.push(item.logo_media_id);
        if (typeof item.photo_media_id === "number") ids.push(item.photo_media_id);
      }
    }
  }
  return ids;
}

function toEmbedUrl(source: "youtube" | "vimeo", url: string): string {
  if (source === "youtube") {
    const match = url.match(/(?:youtu\.be\/|v=|embed\/)([\w-]{11})/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : url;
  }
  const match = url.match(/vimeo\.com\/(\d+)/);
  return match ? `https://player.vimeo.com/video/${match[1]}` : url;
}

/** Splits a "text" block's body on its first blank line into a heading + rest, e.g. "Our Vision\n\nTo be..." */
function splitHeadingBody(body: string): { heading: string | null; rest: string } {
  const match = body.match(/^(.+?)\n\n([\s\S]*)$/);
  if (!match) return { heading: null, rest: body };
  return { heading: match[1].trim(), rest: match[2].trim() };
}

/** Renders a Page's ordered, active blocks — one component per `block_type`, matching the Page Builder's own 11 shapes exactly. */
export function PageBlockRenderer({ blocks }: { blocks: PageBlock[] }) {
  const media = useResolvedMedia(collectMediaIds(blocks));
  const active = blocks.filter((b) => b.is_active).sort((a, b) => a.order - b.order);

  // Consecutive "text" blocks (e.g. Vision + Mission) render together as a
  // side-by-side card grid instead of stacked full-width paragraphs. A
  // lone "text" block still renders on its own via the default case.
  const groups: PageBlock[][] = [];
  for (const block of active) {
    const prevGroup = groups[groups.length - 1];
    if (block.block_type === "text" && prevGroup?.[0]?.block_type === "text") {
      prevGroup.push(block);
    } else {
      groups.push([block]);
    }
  }

  return (
    <div className="flex flex-col">
      {groups.map((group) =>
        group.length > 1 ? (
          <TextCardGrid key={group[0].id} blocks={group} />
        ) : (
          <Block key={group[0].id} block={group[0]} media={media} />
        )
      )}
    </div>
  );
}

function TextCardGrid({ blocks }: { blocks: PageBlock[] }) {
  return (
    <section className="py-10">
      <div
        className={cn(
          "mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 lg:px-8",
          blocks.length === 2 ? "sm:grid-cols-2" : blocks.length === 3 ? "sm:grid-cols-3" : "sm:grid-cols-2"
        )}
      >
        {blocks.map((block) => {
          const { heading, rest } = splitHeadingBody(block.data.body as string);
          return (
            <div
              key={block.id}
              className="rounded-xl border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] p-6"
            >
              <span className="block h-1 w-10 rounded-full bg-gold" />
              {heading && (
                <h3 className="mt-4 font-display text-h4 font-semibold text-[color:var(--color-text)]">{heading}</h3>
              )}
              <p className="mt-2 text-body leading-relaxed text-neutral-600 dark:text-white/70">{rest}</p>
            </div>
          );
        })}
      </div>
    </section>
  );
}

function Block({ block, media }: { block: PageBlock; media: ReturnType<typeof useResolvedMedia> }) {
  switch (block.block_type) {
    case "hero": {
      const data = block.data as unknown as HeroBlockData;
      const image = data.media_id ? media.get(data.media_id) : undefined;
      return (
        <section className="relative flex min-h-[340px] items-center overflow-hidden bg-gradient-to-br from-navy via-navy-dark to-navy text-white">
          {image ? (
            <>
              <img src={image.url} alt="" aria-hidden="true" className="absolute inset-0 h-full w-full object-cover" />
              <div className="absolute inset-0 bg-gradient-to-b from-navy-dark/85 via-navy-dark/70 to-navy-dark/90" />
            </>
          ) : (
            <>
              {/* Decorative accent so the plain-navy case doesn't look empty */}
              <div className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-gold/10 blur-3xl" />
              <div className="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-gold/5 blur-3xl" />
            </>
          )}
          <div
            className={cn(
              "relative mx-auto flex w-full max-w-5xl flex-col gap-4 px-4 py-20",
              data.alignment === "left" && "items-start text-left",
              data.alignment === "right" && "items-end text-right",
              (!data.alignment || data.alignment === "center") && "items-center text-center"
            )}
          >
            <span className="h-1 w-14 rounded-full bg-gold" />
            <h1 className="font-display text-h1 font-semibold">{data.heading}</h1>
            {data.subheading && <p className="max-w-2xl text-body-lg text-white/85">{data.subheading}</p>}
            {data.cta_url && data.cta_label && (
              <SmartLink
                to={data.cta_url}
                className="mt-2 inline-flex h-11 items-center rounded bg-gold px-6 text-body-sm font-semibold text-navy-dark hover:bg-gold-tint"
              >
                {data.cta_label}
              </SmartLink>
            )}
          </div>
        </section>
      );
    }

    case "text": {
      const { heading, rest } = splitHeadingBody(block.data.body as string);
      return (
        <div className="mx-auto max-w-3xl px-4 py-8 text-body text-[color:var(--color-text)]">
          {heading && <h2 className="mb-2 font-display text-h3 font-semibold">{heading}</h2>}
          <p className="whitespace-pre-line">{rest}</p>
        </div>
      );
    }

    case "rich_text":
      return (
        <div
          className={cn("mx-auto max-w-3xl px-4 py-8", RICH_TEXT_CLASSNAME)}
          dangerouslySetInnerHTML={{ __html: block.data.body as string }}
        />
      );

    case "chairman_message": {
      const data = block.data as unknown as ChairmanMessageBlockData;
      const photo = data.media_id ? media.get(data.media_id) : undefined;
      const isManager = /manager/i.test(data.role ?? "");
      const heading = data.heading ?? (isManager ? "A Message from Our Manager" : "A Message from Our Chairman");

      return (
        <section className={cn("py-12", isManager && "bg-[color:var(--pub-paper-tint)]")}>
          <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div className="mb-6 text-center md:text-left">
              <span className="font-sans text-caption font-semibold uppercase tracking-widest text-gold">Leadership</span>
              <h2 className="mt-1 font-display text-h2 font-semibold text-[color:var(--color-text)]">{heading}</h2>
            </div>
            <div className={cn("flex flex-col gap-8 md:items-stretch", isManager ? "md:flex-row-reverse" : "md:flex-row")}>
              {photo && (
                <div className="flex-shrink-0 md:w-[24rem]">
                  <img
                    src={photo.url}
                    alt={data.name ?? ""}
                    loading="lazy"
                    decoding="async"
                    className="h-72 w-full rounded-2xl object-cover shadow-2 md:h-full"
                  />
                </div>
              )}
              <div className="relative flex flex-col justify-center gap-4">
                <span aria-hidden="true" className="font-display text-h1 leading-none text-gold/30">
                  &ldquo;
                </span>
                {data.message && (
                  <p className="-mt-6 whitespace-pre-line text-body leading-relaxed text-[color:var(--color-text)]">
                    {data.message}
                  </p>
                )}
                {(data.name || data.role) && (
                  <footer className="border-t border-[color:var(--pub-line)] pt-3">
                    {data.name && <p className="font-display text-h4 font-medium text-[color:var(--color-text)]">{data.name}</p>}
                    {data.role && <p className="text-body-sm text-neutral-500">{data.role}</p>}
                  </footer>
                )}
              </div>
            </div>
          </div>
        </section>
      );
    }

    case "image": {
      const data = block.data as unknown as ImageBlockData;
      const image = data.media_id ? media.get(data.media_id) : undefined;
      if (!image) return null;
      return (
        <figure className="mx-auto max-w-4xl px-4 py-8">
          <img src={image.url} alt={data.caption ?? ""} loading="lazy" decoding="async" className="w-full rounded-lg" />
          {data.caption && <figcaption className="mt-2 text-center text-body-sm text-neutral-500">{data.caption}</figcaption>}
        </figure>
      );
    }

    case "gallery": {
      const data = block.data as unknown as GalleryBlockData;
      return (
        <div className="mx-auto max-w-6xl px-4 py-8">
          <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            {data.media_ids.map((id) => {
              const item = media.get(id);
              if (!item) return null;
              return (
                <img key={id} src={item.thumb_url ?? item.url} alt="" loading="lazy" decoding="async" className="aspect-square w-full rounded-lg object-cover" />
              );
            })}
          </div>
        </div>
      );
    }

    case "video": {
      const data = block.data as unknown as VideoBlockData;
      const uploaded = data.source === "upload" && data.media_id ? media.get(data.media_id) : undefined;
      return (
        <div className="mx-auto max-w-4xl px-4 py-8">
          <div className="aspect-video overflow-hidden rounded-lg bg-black">
            {data.source === "upload" ? (
              uploaded && <video src={uploaded.url} controls className="h-full w-full" />
            ) : (
              data.url && (
                <iframe
                  src={toEmbedUrl(data.source, data.url)}
                  title={data.caption ?? "Video"}
                  allowFullScreen
                  className="h-full w-full"
                />
              )
            )}
          </div>
          {data.caption && <p className="mt-2 text-center text-body-sm text-neutral-500">{data.caption}</p>}
        </div>
      );
    }

    case "cta": {
      const data = block.data as unknown as CtaBlockData;
      const isPrimary = (data.style ?? "primary") === "primary";
      return (
        <section className={cn("px-4 py-12 text-center", isPrimary ? "bg-navy text-white" : "bg-gold/10")}>
          <div className="mx-auto flex max-w-2xl flex-col items-center gap-3">
            <h2 className="font-display text-h2 font-semibold">{data.heading}</h2>
            {data.body && <p className={cn("text-body", isPrimary ? "text-white/85" : "text-neutral-600")}>{data.body}</p>}
            <SmartLink
              to={data.button_url}
              className={cn(
                "mt-2 inline-flex h-11 items-center rounded px-6 text-body-sm font-semibold",
                isPrimary ? "bg-gold text-navy-dark hover:bg-gold-tint" : "bg-navy text-white hover:bg-navy-light"
              )}
            >
              {data.button_label}
            </SmartLink>
          </div>
        </section>
      );
    }

    case "faq": {
      const items = (block.data.items as FaqItem[]) ?? [];
      if (items.length === 0) return null;
      return (
        <div className="mx-auto max-w-3xl px-4 py-8">
          <Accordion items={items.map((item, i) => ({ id: i, question: item.question, answer: item.answer }))} />
        </div>
      );
    }

    case "statistics": {
      const items = (block.data.items as StatisticItem[]) ?? [];
      return (
        <div className="mx-auto grid max-w-5xl grid-cols-2 gap-6 px-4 py-12 sm:grid-cols-4">
          {items.map((item, i) => (
            <div key={i} className="flex flex-col items-center text-center">
              <span className="font-display text-h1 font-semibold text-navy dark:text-white">{item.value}</span>
              <span className="mt-1 text-body-sm text-neutral-500">{item.label}</span>
            </div>
          ))}
        </div>
      );
    }

    case "testimonials": {
      const items = (block.data.items as TestimonialItem[]) ?? [];
      return (
        <div className="mx-auto grid max-w-5xl gap-6 px-4 py-12 sm:grid-cols-2">
          {items.map((item, i) => {
            const avatar = item.avatar_media_id ? media.get(item.avatar_media_id) : undefined;
            return (
              <blockquote key={i} className="rounded-lg border border-[color:var(--color-border)] p-6">
                <p className="text-body italic text-[color:var(--color-text)]">&ldquo;{item.quote}&rdquo;</p>
                <footer className="mt-4 flex items-center gap-3">
                  {avatar && <img src={avatar.thumb_url ?? avatar.url} alt="" loading="lazy" decoding="async" className="h-10 w-10 rounded-full object-cover" />}
                  <div>
                    <p className="text-body-sm font-semibold text-[color:var(--color-text)]">{item.name}</p>
                    {item.role && <p className="text-caption text-neutral-500">{item.role}</p>}
                  </div>
                </footer>
              </blockquote>
            );
          })}
        </div>
      );
    }

    case "partners": {
      const items = (block.data.items as PartnerItem[]) ?? [];
      return (
        <div className="mx-auto flex max-w-5xl flex-wrap items-center justify-center gap-10 px-4 py-12">
          {items.map((item, i) => {
            const logo = item.logo_media_id ? media.get(item.logo_media_id) : undefined;
            const content = logo ? (
              <img src={logo.url} alt={item.name} loading="lazy" decoding="async" className="h-12 w-auto grayscale hover:grayscale-0" />
            ) : (
              item.name
            );
            return item.url ? (
              <a key={i} href={item.url} target="_blank" rel="noopener noreferrer">
                {content}
              </a>
            ) : (
              <span key={i}>{content}</span>
            );
          })}
        </div>
      );
    }

    case "management_board": {
      const items = (block.data.items as DirectorItem[]) ?? [];
      if (items.length === 0) return null;
      return (
        <section className="py-12">
          <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div className="mb-8 text-center">
              <span className="font-sans text-caption font-semibold uppercase tracking-widest text-gold">Leadership</span>
              <h2 className="mt-1 font-display text-h2 font-semibold text-[color:var(--color-text)]">Management Board</h2>
            </div>
            <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
              {items.map((item, i) => {
                const photo = item.photo_media_id ? media.get(item.photo_media_id) : undefined;
                return (
                  <div key={i} className="flex flex-col overflow-hidden rounded-xl border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] shadow-1">
                    {photo && (
                      <img
                        src={photo.url}
                        alt={item.name}
                        loading="lazy"
                        decoding="async"
                        className="aspect-[4/5] w-full object-cover"
                      />
                    )}
                    <div className="p-4 text-center">
                      <p className="font-display text-h4 font-medium text-[color:var(--color-text)]">{item.name}</p>
                      <p className="text-body-sm text-neutral-500">{item.position}</p>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </section>
      );
    }

    default:
      return null;
  }
}