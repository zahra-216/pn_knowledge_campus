<!doctype html>
<html>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <p>Hi {{ $inquiry->name }},</p>
    <p>Thanks for reaching out to PNK Global Campus. We've received your message and a member of our team will be in touch shortly.</p>
    <p style="color: #666; font-size: 13px;">Your message: "{{ $inquiry->message }}"</p>
</body>
</html>
