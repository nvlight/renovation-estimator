import { expect, describe, it } from 'vitest'
import { sum } from '@/libs/vitest-lib.js'

describe('sum-test.js', () => {
  it('adds 1 + 2 to equal 3', () => {
    expect(sum(1, 2)).toBe(3);
  });
  it('adds 5 + 3 to equal 8', () => {
    expect(sum(5, 3)).toBe(8);
  });
  it('adds 9 + 2 to equal 11', () => {
    expect(sum(9, 2)).toBe(11);
  });

})
