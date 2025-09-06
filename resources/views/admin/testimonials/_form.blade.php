<div class="space-y-4">
  <div>
    <label class="block text-sm font-medium">Name *</label>
    <input name="name" value="{{ old('name', $testimonial->name ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md" required />
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium">Role</label>
      <input name="role" value="{{ old('role', $testimonial->role ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md" />
    </div>
    <div>
      <label class="block text-sm font-medium">Company</label>
      <input name="company" value="{{ old('company', $testimonial->company ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md" />
    </div>
  </div>
  <div>
    <label class="block text-sm font-medium">Avatar (URL)</label>
    <input name="avatar" value="{{ old('avatar', $testimonial->avatar ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md" />
  </div>
  <div>
    <label class="block text-sm font-medium">Quote *</label>
    <textarea name="quote" rows="4" class="mt-1 w-full border-gray-300 rounded-md" required>{{ old('quote', $testimonial->quote ?? '') }}</textarea>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium">Display Order</label>
      <input type="number" min="0" name="display_order" value="{{ old('display_order', $testimonial->display_order ?? 0) }}" class="mt-1 w-full border-gray-300 rounded-md" />
    </div>
    <div class="flex items-center mt-6">
      <input type="checkbox" name="is_active" value="1" class="mr-2" {{ old('is_active', ($testimonial->is_active ?? true)) ? 'checked' : '' }} /> Active
    </div>
  </div>
</div>
