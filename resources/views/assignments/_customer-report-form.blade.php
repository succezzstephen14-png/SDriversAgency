<form method="POST" action="{{ route($routeName, ['type' => $assignmentType, 'id' => $assignment->getKey()]) }}" class="border rounded p-3 mt-3">
    @csrf
    <input type="hidden" name="entry_type" value="customer_report">
    <h3 class="h6">Report an issue or share feedback about the assigned driver</h3>
    <label class="form-label" for="customer-rating-{{ $assignmentType }}-{{ $assignment->getKey() }}">Driver rating (optional)</label>
    <select class="form-select mb-2" id="customer-rating-{{ $assignmentType }}-{{ $assignment->getKey() }}" name="rating">
        <option value="">Choose a rating</option>
        @for($rating = 5; $rating >= 1; $rating--)
            <option value="{{ $rating }}">{{ $rating }} / 5</option>
        @endfor
    </select>
    <label class="form-label" for="customer-report-{{ $assignmentType }}-{{ $assignment->getKey() }}">Message</label>
    <textarea class="form-control mb-2" id="customer-report-{{ $assignmentType }}-{{ $assignment->getKey() }}" name="message" rows="3" minlength="3" maxlength="3000" required></textarea>
    <p class="small text-muted">Your report is private to administrators. They can review it and send feedback to the assignment participants.</p>
    <button class="btn btn-outline-danger" type="submit">Send private report to admin</button>
</form>
