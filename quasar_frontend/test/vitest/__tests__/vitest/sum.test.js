import { expect, describe, it, vi } from 'vitest'
import { sum } from '@/libs/vitest-lib.js'

const fn = vi.fn();

// apt-get update
// apt-get install -y net-tools
// netstat -tuln

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

  it('expect(vi.isMockFunction(fn))', () => {
    fn('hello', 1);
    expect(vi.isMockFunction(fn)).toBe(true)
  });

  it('expect(fn.mock.calls[0])', () => {
    expect(fn.mock.calls[0]).toEqual(['hello', 1])
  })

  //fn('world', 2);
  //expect(fn.mock.results[1].value).toBe('world')
  it('fn.mockImplementation', () => {
    //fn('world', 2);
    //expect(fn.mock.calls[1]).toEqual(['world', 2]);

    fn.mockImplementation((arg1, arg2) => arg1)
    fn('world', 2);
    expect(fn.mock.results[1].value).toBe('world')
  });

});
