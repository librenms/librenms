-- Allow for future use of different tokens - this supports up to SHA-512
ALTER TABLE `api_tokens` MODIFY `token_hash` VARCHAR(256);
