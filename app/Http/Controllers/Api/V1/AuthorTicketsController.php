<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthorTicketsController extends ApiController
{
    public function index($author_id, TicketFilter $filters)
    {
        return TicketResource::collection(
            Ticket::where('user_id', $author_id)->filter($filters)->paginate()
        );
    }

    public function store(StoreTicketRequest $request, string $author_id)
    {
        $model = [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'status' => $request->input('data.attributes.status'),
            'user_id' => $author_id,
        ];

        return new TicketResource(Ticket::create($model));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $author_id, string $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->user_id == $author_id) {
                $ticket->delete();
                return $this->ok('Ticket successfully deleted');
            }

            return $this->error('Ticket cannot be found.', 404);

        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot be found.', 404);
        }
    }
}