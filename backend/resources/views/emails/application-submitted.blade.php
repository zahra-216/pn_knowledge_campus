<!doctype html>
<html>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <p>Hi {{ $application->first_name }},</p>
    <p>Thank you for submitting your application to PN Knowledge Campus. Your reference number is:</p>
    <p style="font-size: 20px; font-weight: bold;">{{ $application->application_number }}</p>
    <p>Please keep this number along with the email address you applied with ({{ $application->email }}) — you'll need both to check your application status later.</p>
    @if ($application->course)
        <p><strong>Course applied for:</strong> {{ $application->course->course_name }}</p>
    @endif
    <p>Our admissions team will review your application and get back to you soon.</p>
</body>
</html>
