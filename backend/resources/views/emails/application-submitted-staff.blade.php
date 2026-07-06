<!doctype html>
<html>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <h2>New Application Submitted</h2>
    <p><strong>Reference:</strong> {{ $application->application_number }}</p>
    <p><strong>Applicant:</strong> {{ $application->first_name }} {{ $application->last_name }}</p>
    <p><strong>Email:</strong> {{ $application->email }}</p>
    @if ($application->phone)
        <p><strong>Phone:</strong> {{ $application->phone }}</p>
    @endif
    @if ($application->course)
        <p><strong>Course:</strong> {{ $application->course->course_name }}</p>
    @endif
    @if ($application->international_applicant)
        <p><strong>International Applicant</strong></p>
    @endif
</body>
</html>
