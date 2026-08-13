<!doctype html>
<html>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <p>Hi {{ $application->first_name }},</p>
    @if ($application->status === 'approved')
        <p>Congratulations! Your application ({{ $application->application_number }}) has been <strong>approved</strong>.</p>
    @else
        <p>Thank you for your interest in PNK Global Campus. After review, your application ({{ $application->application_number }}) was not successful at this time.</p>
    @endif
    @if ($application->review_notes)
        <p><strong>Note from our admissions team:</strong></p>
        <p>{{ $application->review_notes }}</p>
    @endif
</body>
</html>
