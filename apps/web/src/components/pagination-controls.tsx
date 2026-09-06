"use client";

import { ChevronLeft, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";

interface PaginationControlsProps {
  currentPage: number;
  lastPage: number;
  onPageChange: (page: number) => void;
  disabled?: boolean;
}

export function PaginationControls({ currentPage, lastPage, onPageChange, disabled }: PaginationControlsProps) {
  if (lastPage <= 1) return null;

  return (
    <div className="flex items-center justify-center gap-3 pt-2">
      <Button
        type="button"
        variant="outline"
        size="sm"
        disabled={disabled || currentPage <= 1}
        onClick={() => onPageChange(currentPage - 1)}
      >
        <ChevronLeft className="mr-1 h-4 w-4" />
        Previous
      </Button>
      <span className="text-sm text-muted-foreground">
        Page {currentPage} of {lastPage}
      </span>
      <Button
        type="button"
        variant="outline"
        size="sm"
        disabled={disabled || currentPage >= lastPage}
        onClick={() => onPageChange(currentPage + 1)}
      >
        Next
        <ChevronRight className="ml-1 h-4 w-4" />
      </Button>
    </div>
  );
}