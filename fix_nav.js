const fs = require('fs');
const target = 'c:\\Users\\Lenovo Thinkpad\\ion_event\\ISUionic\\src\\views\\CreateReservation.vue';
let content = fs.readFileSync(target, 'utf8');

const searchStr = "router.push('/reservations');";
const replacement = "router.push('/home');";

if (content.includes(searchStr)) {
  // Only replace the FIRST occurrence (the create success one)
  const idx = content.indexOf(searchStr);
  const before = content.slice(0, idx);
  const after = content.slice(idx + searchStr.length);
  content = before + replacement + after;
  fs.writeFileSync(target, content);
  console.log('SUCCESS: changed /reservations to /home');
} else {
  console.log('NOT_FOUND');
}
