<?php

namespace App\Modules\Structure\Http\Services\Dashboard;

use App\Modules\Base\Concerns\HandlesResourceQuery;
use App\Modules\Structure\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageService
{
    use HandlesResourceQuery;

    public function index(Request $request): View
    {
        $query = ContactMessage::query();

        $this->applyDashboardFilters(
            $query,
            $request,
            searchable: ['name', 'email', 'phone', 'message'],
            filterable: ['is_read' => 'is_read'],
        );

        return view('structure::dashboard.contact_messages.index', [
            'messages' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return view('structure::dashboard.contact_messages.show', [
            'message' => $contactMessage,
        ]);
    }

    public function toggleRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['is_read' => ! $contactMessage->is_read]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return to_route('dashboard.contact-messages.index')
            ->with('success', __('messages.deleted_successfully'));
    }
}
