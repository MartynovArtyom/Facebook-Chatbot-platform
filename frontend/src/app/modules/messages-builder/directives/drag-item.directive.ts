import { Directive, EventEmitter, HostListener, Output } from '@angular/core';

@Directive({
  selector: '[appDragItem]'
})
export class DragItemDirective {

  @Output() mouseUpEvent = new EventEmitter<MouseEvent>();
  @Output() mouseDownEvent = new EventEmitter<MouseEvent>();
  @Output() mouseMoveEvent = new EventEmitter<MouseEvent>();

  @HostListener('document:mouseup', ['$event'])
  onMouseup(event: MouseEvent) {
    this.mouseUpEvent.emit(event);
  }

  @HostListener('mousedown', ['$event'])
  onMousedown(event: MouseEvent) {
    this.mouseDownEvent.emit(event);
  }

  @HostListener('document:mousemove', ['$event'])
  onMousemove(event: MouseEvent) {
    this.mouseMoveEvent.emit(event);
  }

}
