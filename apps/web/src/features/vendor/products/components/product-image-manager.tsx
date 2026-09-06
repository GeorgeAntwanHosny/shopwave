"use client";

import { useState } from "react";
import { toast } from "sonner";
import { DndContext, closestCenter, PointerSensor, useSensor, useSensors, type DragEndEvent } from "@dnd-kit/core";
import { SortableContext, arrayMove, rectSortingStrategy, useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { X, GripVertical, Loader2, UploadCloud } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useUploadProductImages } from "@/features/vendor/products/hooks/useUploadProductImages";
import { useDeleteProductImage, useReorderProductImages } from "@/features/vendor/products/hooks/useProductImageMutations";
import { ApiError } from "@/lib/api/client";

interface ProductImage {
  id: number;
  url: string;
  sort_order: number;
}

interface PendingImage {
  key: string;
  file: File;
  previewUrl: string;
}

interface ProductImageManagerProps {
  productId: string;
  images: ProductImage[];
}

const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp", "image/gif"];
const MAX_SIZE_BYTES = 5 * 1024 * 1024;

function SortableThumb({
  image,
  onDelete,
  isDeleting,
}: {
  image: ProductImage;
  onDelete: (id: number) => void;
  isDeleting: boolean;
}) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: image.id });

  return (
    <div
      ref={setNodeRef}
      style={{ transform: CSS.Transform.toString(transform), transition, opacity: isDragging ? 0.5 : 1 }}
      className="group relative aspect-square overflow-hidden rounded-md border border-border bg-muted"
    >
      {/* eslint-disable-next-line @next/next/no-img-element */}
      <img src={image.url} alt="" className="h-full w-full object-cover" />
      <button
        {...attributes}
        {...listeners}
        type="button"
        className="absolute left-1 top-1 rounded bg-background/80 p-1 text-foreground opacity-0 transition-opacity group-hover:opacity-100"
        aria-label="Drag to reorder"
      >
        <GripVertical className="h-4 w-4" />
      </button>
      <button
        type="button"
        onClick={() => onDelete(image.id)}
        disabled={isDeleting}
        className="absolute right-1 top-1 rounded bg-background/80 p-1 text-destructive opacity-0 transition-opacity group-hover:opacity-100 disabled:opacity-50"
        aria-label="Delete image"
      >
        <X className="h-4 w-4" />
      </button>
    </div>
  );
}

export function ProductImageManager({ productId, images }: ProductImageManagerProps) {
  const [localImages, setLocalImages] = useState(images);
  const [pending, setPending] = useState<PendingImage[]>([]);
  const upload = useUploadProductImages(productId);
  const deleteImage = useDeleteProductImage(productId);
  const reorder = useReorderProductImages(productId);
  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }));

  if (localImages.length !== images.length) setLocalImages(images);

  function handleSelectFiles(e: React.ChangeEvent<HTMLInputElement>) {
    const files = e.target.files ? Array.from(e.target.files) : [];
    e.target.value = "";
    if (files.length === 0) return;

    const accepted: PendingImage[] = [];
    const rejected: string[] = [];

    for (const file of files) {
      if (!ACCEPTED_TYPES.includes(file.type)) {
        rejected.push(`${file.name} — unsupported file type`);
        continue;
      }
      if (file.size > MAX_SIZE_BYTES) {
        rejected.push(`${file.name} — larger than 5MB`);
        continue;
      }
      accepted.push({
        key: `${file.name}-${file.size}-${Date.now()}-${Math.random()}`,
        file,
        previewUrl: URL.createObjectURL(file),
      });
    }

    if (rejected.length > 0) toast.error(rejected.join(", "));
    if (accepted.length > 0) setPending((prev) => [...prev, ...accepted]);
  }

  function removePending(key: string) {
    setPending((prev) => {
      const match = prev.find((p) => p.key === key);
      if (match) URL.revokeObjectURL(match.previewUrl);
      return prev.filter((p) => p.key !== key);
    });
  }

  function handleUploadPending() {
    if (pending.length === 0) return;

    upload.mutate(pending.map((p) => p.file), {
      onSuccess: () => {
        toast.success(`${pending.length} image${pending.length > 1 ? "s" : ""} uploaded.`);
        pending.forEach((p) => URL.revokeObjectURL(p.previewUrl));
        setPending([]);
      },
      onError: (error) => {
        const err = error as ApiError;
        const messages = err.fieldErrors ? Object.values(err.fieldErrors).flat().join(" ") : err.message;
        toast.error(messages || "Could not upload images.");
      },
    });
  }

  function handleDelete(id: number) {
    deleteImage.mutate(id, {
      onSuccess: () => toast.success("Image removed."),
      onError: () => toast.error("Could not remove image."),
    });
  }

  function handleDragEnd(event: DragEndEvent) {
    const { active, over } = event;
    if (!over || active.id === over.id) return;

    const oldIndex = localImages.findIndex((img) => img.id === active.id);
    const newIndex = localImages.findIndex((img) => img.id === over.id);
    const reordered = arrayMove(localImages, oldIndex, newIndex);
    setLocalImages(reordered);

    reorder.mutate(reordered.map((img) => img.id), {
      onSuccess: () => toast.success("Image order saved."),
      onError: () => toast.error("Could not save the new order."),
    });
  }

  return (
    <div className="space-y-4">
      <p className="text-sm font-medium text-foreground">Product images</p>

      {localImages.length > 0 && (
        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
          <SortableContext items={localImages.map((i) => i.id)} strategy={rectSortingStrategy}>
            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
              {localImages.map((image) => (
                <SortableThumb key={image.id} image={image} onDelete={handleDelete} isDeleting={deleteImage.isPending} />
              ))}
            </div>
          </SortableContext>
        </DndContext>
      )}

      {pending.length > 0 && (
        <div className="space-y-2">
          <p className="text-xs text-muted-foreground">Ready to upload — not saved yet</p>
          <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
            {pending.map((p) => (
              <div key={p.key} className="group relative aspect-square overflow-hidden rounded-md border border-dashed border-border bg-muted">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={p.previewUrl} alt={p.file.name} className="h-full w-full object-cover opacity-80" />
                <button
                  type="button"
                  onClick={() => removePending(p.key)}
                  className="absolute right-1 top-1 rounded bg-background/80 p-1 text-destructive opacity-0 transition-opacity group-hover:opacity-100"
                  aria-label="Remove"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>
            ))}
          </div>
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3">
        <label className="inline-flex cursor-pointer items-center gap-2 rounded-md border border-dashed border-border px-4 py-2 text-sm text-muted-foreground hover:bg-muted">
          <UploadCloud className="h-4 w-4" />
          Choose images
          <input
            type="file"
            accept="image/png,image/jpeg,image/webp,image/gif"
            multiple
            className="hidden"
            onChange={handleSelectFiles}
          />
        </label>

        {pending.length > 0 && (
          <Button type="button" size="sm" onClick={handleUploadPending} disabled={upload.isPending}>
            {upload.isPending ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Uploading...
              </>
            ) : (
              `Upload ${pending.length} image${pending.length > 1 ? "s" : ""}`
            )}
          </Button>
        )}
      </div>
    </div>
  );
}