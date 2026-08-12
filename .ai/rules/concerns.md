---
paths:
  - 'app/Modules/Base/Concerns/**'
---

# Concerns

## HasImages: persist-then-touch-disk ordering
HasImages::putImage() writes the file, then save()s the column inside a try/catch that deletes the just-written file if save() throws (no orphan), and only deletes the PREVIOUS file after a successful save. deleteImage() nulls the column FIRST, then deletes the file — so a storage failure leaves an orphaned file (harmless) rather than a row pointing at a missing file. This ordering also makes Manager/User destroy safe without wrapping deleteImage in the delete transaction. Do not revert to delete-file-before-save or write-file-after-save. Exports stream via ->cursor() (not ->get()) inside the streamDownload closure.
