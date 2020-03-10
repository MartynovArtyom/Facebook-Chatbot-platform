import { Directive, Output, HostListener, EventEmitter } from '@angular/core';

@Directive({ selector: '[appWheel]' })
export class MouseWheelDirective {
  @Output() wheelDirection = new EventEmitter();

  @HostListener('mousewheel', ['$event']) onMouseWheelChrome(event: any) {
    this.mouseWheelFunc(event);
  }

  @HostListener('DOMMouseScroll', ['$event']) onMouseWheelFirefox(event: any) {
    this.mouseWheelFunc(event);
  }

  @HostListener('onmousewheel', ['$event']) onMouseWheelIE(event: any) {
    this.mouseWheelFunc(event);
  }

  mouseWheelFunc(event: any) {
    const e = window.event || event;
    const delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
    if (delta > 0) {
      this.wheelDirection.emit({direction: -1, event: event});
    } else if (delta < 0) {
      this.wheelDirection.emit({direction: 1, event: event});
    }
    e.returnValue = false;
    if (e.preventDefault) {
      e.preventDefault();
    }
  }

}
