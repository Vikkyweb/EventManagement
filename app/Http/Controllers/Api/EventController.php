<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::all();
        // return response()->json([
        //     'message' => 'List of events',
        //     'data' => $events
        // ]);

        $query = Event::query();

        $relations = ['user', 'attendees', 'attendees.user'];
        // $this->shouldIncludeRelation('user');
        foreach ($relations as $relation) {
           $query->when(
            $this->shouldIncludeRelation($relation), 
            function ($q) use ($relation) {
                $q->with($relation);
            });
        }
        // return EventResource::collection(Event::with('user','attendees')->paginate());
        return EventResource::collection($query->latest()-> paginate());
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $includeRelations = request()->query('include', '');

        if(!$includeRelations){
            return false;
        }

        $relations = array_map('trim', explode(',', $includeRelations));

        return in_array($relation, $relations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
 

        $event = Event::create([
            ... $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            ]),

            'user_id' => 1
        ]);

        // return response()->json([
        //     'message' => 'Event created successfully',
        //     'data' => $event
        // ], 201);

        $event->load('user');
        return new EventResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $event->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after_or_equal:start_time',
        ]));

        $event->load('user');
        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
        // return response()->json([
        //     'message' => 'Event deleted successfully'
        // ]);
    }
}
