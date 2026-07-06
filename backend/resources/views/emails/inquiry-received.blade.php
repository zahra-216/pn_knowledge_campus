<!doctype html>
<html>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <h2>New Inquiry Received</h2>
    <p><strong>Name:</strong> {{ $inquiry->name }}</p>
    <p><strong>Email:</strong> {{ $inquiry->email }}</p>
    @if ($inquiry->phone)
        <p><strong>Phone:</strong> {{ $inquiry->phone }}</p>
    @endif
    @if ($inquiry->course)
        <p><strong>Course:</strong> {{ $inquiry->course->course_name }}</p>
    @endif
    @if ($inquiry->international_applicant)
        <p><strong>International Applicant</strong></p>
    @endif
    @if ($inquiry->source_page)
        <p><strong>Submitted from:</strong> {{ $inquiry->source_page }}</p>
    @endif
    <p><strong>Message:</strong></p>
    <p>{{ $inquiry->message }}</p>
</body>
</html>
