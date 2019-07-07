<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of Constants
 *
 * @author yaklimuk
 */
class Constants
{

    const H_BCC = 'Bcc';
    const H_CC = 'Cc';
    const H_DATE = 'Date';
    const H_DKIM_SIGNATURE = 'DKIM-Signature';
    const H_FROM = 'From';
    const H_MESSAGE_ID = 'Message-ID';
    const H_REPLYTO = 'Reply-To';
    const H_RECEIVED = 'Received';
    const H_SUBJECT = 'Subject';
    const H_TO = 'To';
    
    const H_CONTENT_TYPE = 'Content-Type';
    const H_TRANSFER_ENCODING = 'Content-Transfer-Encoding';
    const H_CONTENT_DISPOSITION = 'Content-Disposition';
    const H_CONTENT_ID = 'Content-Id';

    const TR_ENC_7BIT = '7bit';
    const TR_ENC_QUOTED_PRINTABLE = 'quoted-printable';
    const TR_ENC_BASE64 = 'base64';
    const TR_ENC_8BIT = '8bit';
    const TR_ENC_BINARY = 'binary';

    const CT_MULTIPART_MIXED = 'multipart/mixed';
    const CT_MULTIPART_RELATED = 'multipart/related';
    const CT_MULTIPART_ALTER = 'multipart/alternative';

    const CT_TEXT_PLAIN = 'text/plain';
    const CT_TEXT_HTML = 'text/html';

    const CD_ATTACHMENT = 'attachment';
    const CD_INLINE = 'inline';

    const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
}
